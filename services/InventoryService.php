<?php

declare(strict_types=1);

/**
 * Every change to stock levels, and low-stock detection.
 *
 * Owner: Kyle Tarus (M2)
 * Sale and void movements: David Muturi (M6)
 * Tasks: 2.6, 2.7, 6.1, 6.4
 */
final class InventoryService
{
    /**
     * Applies a restock, damage or correction and records the movement. Returns the new stock level.
     */
    public function adjustStock(int $productId, string $type, int $quantity, string $reason, int $userId): int
    {
        throw new LogicException('Not implemented: task 2.6.');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getLowStock(): array
    {
        throw new LogicException('Not implemented: task 2.7.');
    }

    /**
     * Runs inside the checkout transaction started by CheckoutService.
     *
     * @param list<array{product_id: int, qty: int}> $items
     */
    public function deductForSale(int $saleId, array $items, int $userId): void
    {
        throw new LogicException('Not implemented: task 6.1.');
    }

    public function restoreForVoid(int $saleId, int $userId): void
    {
        throw new LogicException('Not implemented: task 6.4.');
    }
}
