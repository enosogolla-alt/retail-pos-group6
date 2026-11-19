<?php
class Product {
    public function find(int $id): ?array { return q('SELECT * FROM products WHERE id=?', [$id])->fetch() ?: null; }
    public function findByCode(string $code): ?array {
        $s = q('SELECT * FROM products WHERE is_active=1 AND (barcode=? OR name LIKE ?) ORDER BY barcode=? DESC LIMIT 1', [$code, "%$code%", $code]);
        return $s->fetch() ?: null;
    }
    public function allWithCategory(): array {
        return q('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.name')->fetchAll();
    }
    public function lowStock(): array { return q('SELECT * FROM products WHERE is_active=1 AND stock_qty <= reorder_level ORDER BY stock_qty')->fetchAll(); }
    public function create(string $bc, string $name, ?int $cat, float $price, int $reorder): int {
        q('INSERT INTO products(barcode,name,category_id,selling_price,reorder_level) VALUES (?,?,?,?,?)', [$bc, $name, $cat, $price, $reorder]);
        return (int)db()->lastInsertId();
    }
    public function update(int $id, string $name, ?int $cat, float $price, int $reorder): void {
        q('UPDATE products SET name=?, category_id=?, selling_price=?, reorder_level=? WHERE id=?', [$name, $cat, $price, $reorder, $id]);
    }
    public function toggle(int $id): void { q('UPDATE products SET is_active = 1 - is_active WHERE id=?', [$id]); }
    public function adjustStock(int $id, int $delta): void { q('UPDATE products SET stock_qty = stock_qty + ? WHERE id=?', [$delta, $id]); }
}
