<?php
class ReceiptController {
    private function show(?array $data, bool $copy): void {
        if (!$data) { http_response_code(404); require __DIR__ . '/../views/errors/404.php'; return; }
        extract($data); $isCopy = $copy; require __DIR__ . '/../views/receipts/receipt.php';
    }
    public function receipt(string $no): void { Auth::requireLogin(); $this->show((new ReceiptService())->build($no), false); }
    public function reprint(string $no): void { Auth::requireLogin(); $this->show((new ReceiptService())->reprint($no, Auth::user()['id']), true); }
    public function voidSale(string $id): void {
        Auth::requireRole('Admin'); $reason = trim($_POST['reason'] ?? '');
        if ($reason === '') { flash('error', 'Void reason required.'); redirect('admin/reports'); }
        $id = (int)$id; $db = db(); $db->beginTransaction();
        try {
            $sale = (new Sale())->find($id);
            if (!$sale || $sale['status'] === 'VOID') throw new RuntimeException('Sale already void or missing.');
            foreach ((new SaleItem())->bySale($id) as $it) {
                (new Product())->adjustStock((int)$it['product_id'], (int)$it['qty']);
                (new StockMovement())->create((int)$it['product_id'], (int)$it['qty'], 'VOID', "Void of {$sale['receipt_no']}", Auth::user()['id']);
            }
            (new Sale())->void($id, $reason, Auth::user()['id']);
            AuditLogger::log(Auth::user()['id'], 'SALE_VOID', "sale={$sale['receipt_no']} reason=$reason");
            $db->commit(); flash('success', 'Sale voided, stock restored.');
        } catch (Throwable $e) { $db->rollBack(); flash('error', $e->getMessage()); }
        redirect('admin/reports');
    }
}
