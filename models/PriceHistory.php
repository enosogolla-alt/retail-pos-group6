<?php

declare(strict_types=1);

/**
 * Table: price_history.
 *
 * Owner: Kyle Tarus (M2)
 * Tasks: 2.5
 */
final class PriceHistory extends Model
{
    public static function record(int $productId, string $oldPrice, string $newPrice, int $changedBy): void
    {
        throw new LogicException('Not implemented: task 2.5.');
    }

    public static function forProduct(int $productId): array
    {
        throw new LogicException('Not implemented: task 2.5.');
    }
}
