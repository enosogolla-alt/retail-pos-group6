<?php

declare(strict_types=1);

/**
 * Table: sales.
 *
 * Owner: Winston Enos (M4)
 * Voids and reports: David Muturi (M6)
 * Tasks: 4.5, 4.6, 6.4, 6.5
 */
final class Sale extends Model
{
    public static function create(array $data): int
    {
        throw new LogicException('Not implemented: task 4.5.');
    }

    public static function findById(int $id): ?array
    {
        throw new LogicException('Not implemented: task 4.5.');
    }

    public static function findByReceiptNo(string $receiptNo): ?array
    {
        throw new LogicException('Not implemented: task 6.2.');
    }

    public static function forDate(string $date): array
    {
        throw new LogicException('Not implemented: task 6.5.');
    }

    /**
     * Format: RCP-YYYYMMDD-0001.
     */
    public static function nextReceiptNo(string $date): string
    {
        throw new LogicException('Not implemented: task 4.6.');
    }

    public static function markVoid(int $id, string $reason, int $voidedBy): void
    {
        throw new LogicException('Not implemented: task 6.4.');
    }
}
