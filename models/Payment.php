<?php
class Payment {
    public function create(int $sid, string $method, float $amount, ?string $code): void {
        q('INSERT INTO payments(sale_id,method,amount,mpesa_code) VALUES (?,?,?,?)', [$sid, $method, $amount, $code]);
    }
    public function bySale(int $sid): array { return q('SELECT * FROM payments WHERE sale_id=?', [$sid])->fetchAll(); }
}
