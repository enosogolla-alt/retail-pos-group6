<?php

declare(strict_types=1);

/**
 * Checkout terminal, payments, receipts and voids.
 *
 * Owner: Winston Enos (M4)
 * Interface: Stacy Kwamboka (M3), Andria Shikwe (M5)
 * Receipts and voids: David Muturi (M6)
 * Tasks: 3.1-3.5, 4.1-4.6, 5.1-5.4, 6.2, 6.4
 */
final class SaleController
{
    public function checkout(): void
    {
        Auth::requireRole('Cashier');

        view('pos/checkout', [
            'title' => 'Checkout',
            'quickProducts' => array_slice(Product::active(), 0, 6),
        ], 'pos');
    }

    /**
     * The signed-in cashier's own sales for one day, with the cash and M-Pesa they should hand over.
     */
    public function history(): void
    {
        Auth::requireRole('Cashier');

        $today = date('Y-m-d');
        $date = input('date');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1 || $date > $today) {
            $date = $today;
        }

        view('pos/my_sales', [
            'title' => 'My sales',
            'section' => 'My sales',
            'date' => $date,
            'isToday' => $date === $today,
            'sales' => Sale::forCashier((int) Auth::id(), $date),
            'summary' => Sale::summaryForDate($date, (int) Auth::id()),
        ], 'pos');
    }

    /**
     * An exact barcode wins; otherwise the best name match. Returns 404 when nothing matches.
     */
    public function findProduct(): void
    {
        Auth::requireRole('Cashier');

        $code = input('code');
        if ($code === '') {
            json(['error' => 'Enter a barcode or product name.'], 422);
        }

        $product = Product::findByBarcode($code) ?? (Product::searchByName($code, 1)[0] ?? null);
        if ($product === null) {
            json(['error' => 'No product matches "' . $code . '".'], 404);
        }

        json(['product' => [
            'id' => (int) $product['id'],
            'barcode' => $product['barcode'],
            'name' => $product['name'],
            'price' => (float) $product['selling_price'],
            'stock' => (int) $product['stock_qty'],
            'active' => (int) $product['is_active'] === 1,
        ]]);
    }

    public function createSale(): void
    {
        Auth::requireRole('Cashier');

        $data = jsonInput();

        try {
            $result = (new CheckoutService())->checkout(
                is_array($data['items'] ?? null) ? $data['items'] : [],
                is_array($data['payment'] ?? null) ? $data['payment'] : [],
                $data['discount_percent'] ?? 0,
                (int) Auth::id()
            );
        } catch (DomainException $exception) {
            json(['error' => $exception->getMessage()], 422);
        }

        json($result + ['receipt_url' => url('/receipts/' . $result['receipt_no'])], 201);
    }

    public function sendMpesaPrompt(): void
    {
        Auth::requireRole('Cashier');

        $data = jsonInput();

        try {
            json((new MpesaService())->stkPush((string) ($data['phone'] ?? ''), (string) ($data['amount'] ?? '0')));
        } catch (DomainException $exception) {
            json(['error' => $exception->getMessage()], 422);
        }
    }

    public function mpesaStatus(): void
    {
        Auth::requireRole('Cashier');

        json((new MpesaService())->status(input('id')));
    }

    public function mpesaCallback(): void
    {
        (new MpesaService())->handleCallback(jsonInput());

        json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function receipt(string $receiptNo): void
    {
        Auth::requireLogin();

        // A cashier may only open receipts for their own sales.
        if (Auth::hasRole('Cashier')) {
            $sale = Sale::findByReceiptNo($receiptNo);
            if ($sale !== null && (int) $sale['user_id'] !== (int) Auth::id()) {
                http_response_code(403);
                view('errors/403', ['title' => 'Access denied'], 'guest');
                return;
            }
        }

        $isCopy = input('copy') === '1';
        $service = new ReceiptService();
        $receipt = $isCopy ? $service->reprint($receiptNo, (int) Auth::id()) : $service->build($receiptNo);

        if ($receipt === null) {
            http_response_code(404);
            view('errors/404', ['title' => 'Receipt not found'], 'guest');
            return;
        }

        view('receipts/receipt', [
            'title' => 'Receipt ' . $receiptNo,
            'receipt' => $receipt,
            'isCopy' => $isCopy,
            'isNewSale' => input('new') === '1',
        ], 'guest');
    }

    public function voidSale(string $id): void
    {
        Auth::requireRole('Admin');

        try {
            $receiptNo = (new VoidService())->void((int) $id, input('reason'), (int) Auth::id());
            flash('success', $receiptNo . ' voided. Stock has been restored.');
        } catch (DomainException $exception) {
            flash('error', $exception->getMessage());
        }

        $date = input('date');
        redirect('/admin/reports' . (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 ? '?date=' . $date : ''));
    }
}
