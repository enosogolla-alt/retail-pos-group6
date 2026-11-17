<?php

declare(strict_types=1);

/**
 * Table: sale_items. Stores the unit price at the time of sale (BR-3).
 *
 * Owner: Winston Enos (M4)
 * Tasks: 4.5
 */
final class SaleItem extends Model
{
    /**
     * @param list<array{product_id: int, qty: int, unit_price: string}> $items
     */
    public static function createMany(int $saleId, array $items): void
    {
        throw new LogicException('Not implemented: task 4.5.');
    }

    public static function forSale(int $saleId): array
    {
        throw new LogicException('Not implemented: task 6.2.');
    }
}
