<?php

declare(strict_types=1);

/**
 * Receipt data for display, printing and reprints, built from the saved sale only.
 *
 * Owner: David Muturi (M6)
 * Tasks: 6.2
 */
final class ReceiptService
{
    /**
     * @return array<string, mixed>|null
     */
    public function build(string $receiptNo): ?array
    {
        $sale = Sale::findByReceiptNo($receiptNo);
        if ($sale === null) {
            return null;
        }

        $payment = Payment::forSale((int) $sale['id']) ?? [];

        return [
            'shop' => Setting::all(),
            'sale' => $sale,
            'items' => SaleItem::forSale((int) $sale['id']),
            'payment' => $payment,
        ];
    }

    /**
     * Same data as build(), recorded in the audit log because reprints can hide fraud.
     */
    public function reprint(string $receiptNo, int $userId): ?array
    {
        $receipt = $this->build($receiptNo);
        if ($receipt !== null) {
            AuditLogger::log($userId, 'RECEIPT_REPRINT', $receiptNo);
        }

        return $receipt;
    }
}
