<?php
class ReceiptService {
    public function build(string $no): ?array {
        $sale = (new Sale())->findByReceipt($no); if (!$sale) return null;
        $settings = q('SELECT skey, sval FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
        return ['sale' => $sale, 'items' => (new SaleItem())->bySale((int)$sale['id']), 'payments' => (new Payment())->bySale((int)$sale['id']),
                'settings' => ['shop_name' => $settings['shop_name'] ?? 'Retail POS', 'kra_pin' => $settings['kra_pin'] ?? '']];
    }
    public function reprint(string $no, int $uid): ?array {
        $d = $this->build($no); if (!$d) return null;
        AuditLogger::log($uid, 'RECEIPT_REPRINT', $no); return $d;
    }
}
