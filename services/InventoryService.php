<?php
class InventoryService {
    // RESTOCK adds, DAMAGE removes, CORRECTION uses the signed number as typed.
    public function adjust(int $productId, string $type, int $qty, string $reason, int $userId): void {
        if (!in_array($type, ['RESTOCK', 'DAMAGE', 'CORRECTION'], true)) throw new RuntimeException('Unknown adjustment type.');
        if ($qty === 0) throw new RuntimeException('Quantity cannot be zero.');
        if (trim($reason) === '') throw new RuntimeException('A reason is required.');

        $delta = match ($type) {
            'RESTOCK' => abs($qty),
            'DAMAGE'  => -abs($qty),
            default   => $qty,
        };

        $db = db();
        $db->beginTransaction();
        try {
            $p = (new Product())->lock($productId);
            if (!$p) throw new RuntimeException('Product not found.');
            if ($p['stock_qty'] + $delta < 0) throw new RuntimeException("Stock for {$p['name']} can't go below zero (now {$p['stock_qty']}).");
            (new Product())->adjustStock($productId, $delta);
            (new StockMovement())->create($productId, $delta, $type, trim($reason), $userId);
            AuditLogger::log($userId, 'STOCK_' . $type, "product={$p['name']} change=$delta reason=$reason");
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
