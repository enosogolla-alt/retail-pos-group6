<?php
class CheckoutService {
    /** @return array{receipt_no:string,sale_id:int} */
    public function process(int $uid, array $cart, array $pay, float $discPct): array {
        if (!$cart) throw new RuntimeException('Cart is empty.');
        $discPct = max(0, min(10, $discPct));
        $want = [];                                   // merge duplicate lines
        foreach ($cart as $c) { $pid = (int)($c['product_id'] ?? 0); $qty = (int)($c['qty'] ?? 0);
            if ($pid < 1 || $qty < 1) throw new RuntimeException('Invalid cart line.'); $want[$pid] = ($want[$pid] ?? 0) + $qty; }
        $db = db(); $db->beginTransaction();
        try {
            $lines = []; $sub = 0;
            foreach ($want as $pid => $qty) {         // prices and stock come from the DB, never from the browser
                $p = q('SELECT * FROM products WHERE id=? AND is_active=1 FOR UPDATE', [$pid])->fetch();
                if (!$p) throw new RuntimeException('A product in the cart is no longer available.');
                if ($p['stock_qty'] < $qty) throw new RuntimeException("Not enough stock for {$p['name']} (only {$p['stock_qty']} left).");
                $line = round($p['selling_price'] * $qty, 2); $sub += $line; $lines[] = [$p, $qty, $line];
            }
            $disc = round($sub * $discPct / 100, 2); $total = round($sub - $disc, 2); $vat = round($total - $total / 1.16, 2);

            $method = $pay['method'] ?? ''; $amount = (float)($pay['amount'] ?? 0); $code = null;
            if (!in_array($method, ['CASH', 'MPESA'], true)) throw new RuntimeException('Choose a payment method.');
            if ($amount + 0.005 < $total) throw new RuntimeException('Payment is less than the total.');
            if ($method === 'MPESA') {
                $code = trim($pay['mpesa_code'] ?? '');
                $ok = q("SELECT 1 FROM mpesa_requests WHERE mpesa_code=? AND status='PAID'", [$code])->fetch();
                $used = q('SELECT 1 FROM payments WHERE mpesa_code=?', [$code])->fetch();
                if (!$code || !$ok || $used) throw new RuntimeException('M-Pesa payment not confirmed or already used.');
            } else $amount = $total;

            $n = (int)q('SELECT COUNT(*) FROM sales WHERE DATE(created_at)=CURDATE()')->fetchColumn() + 1;
            $no = sprintf('RCP-%s-%04d', date('Ymd'), $n);
            $sid = (new Sale())->create($no, $uid, $sub, $disc, $vat, $total);
            foreach ($lines as [$p, $qty, $line]) {
                (new SaleItem())->create($sid, (int)$p['id'], $p['name'], $qty, (float)$p['selling_price'], $line);
                (new Product())->adjustStock((int)$p['id'], -$qty);
                (new StockMovement())->create((int)$p['id'], -$qty, 'SALE', $no, $uid);
            }
            (new Payment())->create($sid, $method, $amount, $code);
            AuditLogger::log($uid, 'SALE_COMPLETE', "$no total=$total $method");
            $db->commit();
            return ['receipt_no' => $no, 'sale_id' => $sid];
        } catch (Throwable $e) { $db->rollBack(); throw $e; }
    }
}
