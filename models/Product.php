<?php

declare(strict_types=1);

/**
 * Table: products.
 *
 * Owner: Kyle Tarus (M2)
 * Lookup and locking: Winston Enos (M4)
 * Tasks: 2.4, 2.6, 2.7, 4.1, 4.5
 */
final class Product extends Model
{
    public static function findById(int $id): ?array
    {
        throw new LogicException('Not implemented: task 2.4.');
    }

    /**
     * Active products only.
     */
    public static function findByBarcode(string $barcode): ?array
    {
        throw new LogicException('Not implemented: task 4.1.');
    }

    /**
     * Active products only.
     */
    public static function searchByName(string $term, int $limit = 10): array
    {
        throw new LogicException('Not implemented: task 4.1.');
    }

    public static function all(?int $categoryId = null): array
    {
        throw new LogicException('Not implemented: task 2.4.');
    }

    public static function create(array $data): int
    {
        throw new LogicException('Not implemented: task 2.4.');
    }

    public static function update(int $id, array $data): void
    {
        throw new LogicException('Not implemented: task 2.4.');
    }

    public static function setActive(int $id, bool $active): void
    {
        throw new LogicException('Not implemented: task 2.4.');
    }

    /**
     * SELECT ... FOR UPDATE. Call only inside a transaction.
     *
     * @param list<int> $ids
     */
    public static function lockForUpdate(array $ids): array
    {
        throw new LogicException('Not implemented: task 4.5.');
    }

    public static function changeStock(int $id, int $delta): void
    {
        throw new LogicException('Not implemented: task 2.6.');
    }

    public static function lowStock(): array
    {
        throw new LogicException('Not implemented: task 2.7.');
    }
}
