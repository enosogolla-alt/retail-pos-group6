<?php

declare(strict_types=1);

/**
 * Table: stock_movements.
 *
 * Owner: Kyle Tarus (M2)
 * Sale and void movements: David Muturi (M6)
 * Tasks: 2.6, 6.1, 6.4
 */
final class StockMovement extends Model
{
    public static function create(array $data): int
    {
        throw new LogicException('Not implemented: task 2.6.');
    }

    public static function recent(?string $type = null, int $limit = 100): array
    {
        throw new LogicException('Not implemented: task 2.6.');
    }
}
