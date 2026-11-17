<?php
class SaleItem {
    public function create(int $sid, int $pid, string $name, int $qty, float $price, float $line): void {
        q('INSERT INTO sale_items(sale_id,product_id,name,qty,unit_price,line_total) VALUES (?,?,?,?,?,?)', [$sid, $pid, $name, $qty, $price, $line]);
    }
    public function bySale(int $sid): array { return q('SELECT * FROM sale_items WHERE sale_id=?', [$sid])->fetchAll(); }
}
