<?php
class StockMovement {
    public function create(int $pid, int $qty, string $type, string $reason, int $uid): void {
        q('INSERT INTO stock_movements(product_id,type,qty_change,reason,user_id) VALUES (?,?,?,?,?)', [$pid, $type, $qty, $reason, $uid]);
    }
    public function recent(int $n = 50): array {
        return q("SELECT m.*, p.name AS product_name, u.username FROM stock_movements m JOIN products p ON p.id=m.product_id
                  LEFT JOIN users u ON u.id=m.user_id ORDER BY m.id DESC LIMIT " . (int)$n)->fetchAll();
    }
}
