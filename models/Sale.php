<?php
class Sale {
    public function create(string $no, int $cashier, float $sub, float $disc, float $vat, float $total): int {
        q('INSERT INTO sales(receipt_no,cashier_id,subtotal,discount,vat,total) VALUES (?,?,?,?,?,?)', [$no, $cashier, $sub, $disc, $vat, $total]);
        return (int)db()->lastInsertId();
    }
    public function find(int $id): ?array { return q('SELECT * FROM sales WHERE id=?', [$id])->fetch() ?: null; }
    public function findByReceipt(string $no): ?array { return q('SELECT * FROM sales WHERE receipt_no=?', [$no])->fetch() ?: null; }
    public function void(int $id, string $reason, int $uid): void { q("UPDATE sales SET status='VOID', void_reason=?, voided_by=? WHERE id=?", [$reason, $uid, $id]); }
}
