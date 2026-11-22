<?php

declare(strict_types=1);

/**
 * Voids a completed sale: marks it VOID, puts the stock back and records who did it and why.
 *
 * Owner: David Muturi (M6)
 * Tasks: 6.4
 */
final class VoidService
{
    public function void(int $saleId, string $reason, int $userId): string
    {
        $reason = trim($reason);
        if (mb_strlen($reason) < 3) {
            throw new DomainException('Enter a reason for voiding this sale.');
        }

        return transaction(function () use ($saleId, $reason, $userId): string {
            $sale = Sale::findById($saleId);
            if ($sale === null) {
                throw new DomainException('Sale not found.');
            }
            if ($sale['status'] !== 'COMPLETED') {
                throw new DomainException('This sale has already been voided.');
            }

            Sale::markVoid($saleId, $reason, $userId);
            (new InventoryService())->restoreForVoid($saleId, $userId);
            AuditLogger::log($userId, 'SALE_VOID', sprintf('%s · %s · %s', $sale['receipt_no'], kes($sale['total']), $reason));

            return (string) $sale['receipt_no'];
        });
    }
}
