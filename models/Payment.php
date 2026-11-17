<?php

declare(strict_types=1);

/**
 * Table: payments.
 *
 * Owner: Winston Enos (M4)
 * Tasks: 4.4, 4.5
 */
final class Payment extends Model
{
    public static function create(array $data): int
    {
        throw new LogicException('Not implemented: task 4.5.');
    }

    public static function forSale(int $saleId): ?array
    {
        throw new LogicException('Not implemented: task 6.2.');
    }

    public static function mpesaCodeExists(string $code): bool
    {
        throw new LogicException('Not implemented: task 4.4.');
    }
}
