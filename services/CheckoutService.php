<?php

declare(strict_types=1);

/**
 * Completes a sale as a single transaction.
 *
 * Owner: Winston Enos (M4)
 * Tasks: 4.5, 4.6
 */
final class CheckoutService
{
    /**
     * Locks the product rows, verifies stock, records the sale, items and payment,
     * deducts stock and writes the audit entry. Rolls back entirely on any failure.
     *
     * @param list<array{product_id: int, qty: int}> $items
     * @param array{method: string, tendered?: string, mpesa_code?: string} $payment
     * @return array{sale_id: int, receipt_no: string}
     */
    public function checkout(array $items, array $payment, float $discountPercent, int $userId): array
    {
        throw new LogicException('Not implemented: task 4.5.');
    }
}
