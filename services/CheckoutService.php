<?php

declare(strict_types=1);

/**
 * Completes a sale as a single transaction. Prices, totals and stock are always taken from
 * the database; amounts sent by the browser are never trusted.
 *
 * Owner: Winston Enos (M4)
 * Tasks: 4.3, 4.4, 4.5, 4.6
 */
final class CheckoutService
{
    private const MAX_QTY_PER_LINE = 1000;

    /**
     * Records the sale, its items and payment, deducts stock and writes the audit entry.
     * Rolls back entirely if any item is short or the payment is invalid.
     *
     * @param list<array{product_id: mixed, qty: mixed}> $items
     * @param array{method?: mixed, tendered?: mixed, phone?: mixed, mpesa_code?: mixed} $payment
     * @return array{sale_id: int, receipt_no: string, total: string, change: string}
     */
    public function checkout(array $items, array $payment, mixed $discountPercent, int $userId): array
    {
        $quantities = $this->normaliseItems($items);
        $percent = $this->validateDiscount($discountPercent);
        $method = strtoupper((string) ($payment['method'] ?? ''));

        if (!in_array($method, ['CASH', 'MPESA'], true)) {
            throw new DomainException('Choose Cash or M-Pesa.');
        }

        return transaction(function () use ($quantities, $percent, $method, $payment, $userId): array {
            $products = Product::lockForUpdate(array_keys($quantities));

            $lines = [];
            $subtotal = 0;
            foreach ($quantities as $productId => $qty) {
                $product = $products[$productId] ?? null;
                if ($product === null || (int) $product['is_active'] !== 1) {
                    throw new DomainException('A product in the cart is no longer available. Remove it and try again.');
                }
                if ((int) $product['stock_qty'] < $qty) {
                    throw new DomainException(sprintf('Only %d %s in stock.', $product['stock_qty'], $product['name']));
                }

                $unit = self::cents($product['selling_price']);
                $subtotal += $unit * $qty;
                $lines[] = [
                    'product_id' => $productId,
                    'qty' => $qty,
                    'unit_price' => self::amount($unit),
                    'line_total' => self::amount($unit * $qty),
                    'stock_before' => (int) $product['stock_qty'],
                ];
            }

            $discount = (int) round($subtotal * $percent / 100);
            $total = $subtotal - $discount;
            // Prices include VAT, so VAT is extracted from the total rather than added.
            $vat = (int) round($total * (int) config('vat_rate', 16) / (100 + (int) config('vat_rate', 16)));

            $paid = $this->validatePayment($method, $payment, $total);

            $receiptNo = Sale::nextReceiptNo();
            $saleId = Sale::create([
                'receipt_no' => $receiptNo,
                'user_id' => $userId,
                'subtotal' => self::amount($subtotal),
                'discount_percent' => number_format($percent, 2, '.', ''),
                'discount' => self::amount($discount),
                'total' => self::amount($total),
                'vat' => self::amount($vat),
            ]);

            SaleItem::createMany($saleId, $lines);
            Payment::create(['sale_id' => $saleId, 'method' => $method] + $paid);
            (new InventoryService())->deductForSale($saleId, $receiptNo, $lines, $userId);

            AuditLogger::log($userId, 'SALE', sprintf(
                '%s · %s · %s%s',
                $receiptNo,
                kes(self::amount($total)),
                $method === 'CASH' ? 'Cash' : 'M-Pesa',
                $percent > 0 ? ' · discount ' . rtrim(rtrim(number_format($percent, 2), '0'), '.') . '%' : ''
            ));

            return [
                'sale_id' => $saleId,
                'receipt_no' => $receiptNo,
                'total' => self::amount($total),
                'change' => $paid['change_given'],
            ];
        });
    }

    /**
     * Merges repeated products and checks every quantity.
     *
     * @return array<int, int> product id => quantity
     */
    private function normaliseItems(array $items): array
    {
        if ($items === []) {
            throw new DomainException('The cart is empty.');
        }

        $quantities = [];
        foreach ($items as $item) {
            $productId = filter_var($item['product_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $qty = filter_var($item['qty'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($productId === false || $qty === false) {
                throw new DomainException('The cart contains an invalid item.');
            }

            $quantities[$productId] = ($quantities[$productId] ?? 0) + $qty;
            if ($quantities[$productId] > self::MAX_QTY_PER_LINE) {
                throw new DomainException('Quantity is too large for one sale.');
            }
        }

        return $quantities;
    }

    /**
     * Only cashiers sell, and no sale may exceed the shop's discount limit (BR-9).
     */
    private function validateDiscount(mixed $value): float
    {
        $percent = $value === null || $value === '' ? 0.0 : filter_var($value, FILTER_VALIDATE_FLOAT);

        if ($percent === false || $percent < 0 || $percent > 100) {
            throw new DomainException('Enter a discount between 0 and 100%.');
        }

        $limit = (float) config('security.cashier_max_discount', 10);
        if ($percent > $limit) {
            throw new DomainException('The maximum discount is ' . rtrim(rtrim(number_format($limit, 2), '0'), '.') . '%.');
        }

        return (float) $percent;
    }

    /**
     * @return array{amount_tendered: string, change_given: string, mpesa_code: ?string, phone: ?string}
     */
    private function validatePayment(string $method, array $payment, int $total): array
    {
        if ($method === 'CASH') {
            $tendered = filter_var($payment['tendered'] ?? null, FILTER_VALIDATE_FLOAT);
            if ($tendered === false || $tendered < 0) {
                throw new DomainException('Enter the cash amount received.');
            }

            $tenderedCents = self::cents($tendered);
            if ($tenderedCents < $total) {
                throw new DomainException('Amount received is less than the total.');
            }

            return [
                'amount_tendered' => self::amount($tenderedCents),
                'change_given' => self::amount($tenderedCents - $total),
                'mpesa_code' => null,
                'phone' => null,
            ];
        }

        $phone = trim((string) ($payment['phone'] ?? ''));
        $code = strtoupper(trim((string) ($payment['mpesa_code'] ?? '')));

        if (preg_match('/^0[17]\d{8}$/', $phone) !== 1) {
            throw new DomainException('Enter a valid Safaricom number, for example 0712345678.');
        }
        if (preg_match('/^[A-Z0-9]{10}$/', $code) !== 1) {
            throw new DomainException('The M-Pesa code must be exactly 10 letters or numbers.');
        }
        if (Payment::mpesaCodeExists($code)) {
            throw new DomainException('This M-Pesa code has already been used on another sale.');
        }

        return [
            'amount_tendered' => self::amount($total),
            'change_given' => '0.00',
            'mpesa_code' => $code,
            'phone' => $phone,
        ];
    }

    private static function cents(mixed $amount): int
    {
        return (int) round((float) $amount * 100);
    }

    private static function amount(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
