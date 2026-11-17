<?php
class ReportController {
    private function date(): string { $d = $_GET['date'] ?? date('Y-m-d'); return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : date('Y-m-d'); }
    public function daily(): void {
        Auth::requireRole('Admin'); $d = $this->date(); $s = new ReportService();
        render('admin/reports', ['date' => $d, 'summary' => $s->summary($d), 'topProducts' => $s->topProducts($d), 'byCashier' => $s->byCashier($d), 'sales' => $s->sales($d)]);
    }
    public function exportCsv(): void {
        Auth::requireRole('Admin'); $d = $this->date();
        AuditLogger::log(Auth::user()['id'], 'REPORT_EXPORT', $d);
        header('Content-Type: text/csv'); header("Content-Disposition: attachment; filename=sales-$d.csv");
        $o = fopen('php://output', 'w'); fputcsv($o, ['Receipt', 'Cashier', 'Subtotal', 'Discount', 'VAT', 'Total', 'Status', 'Time']);
        foreach ((new ReportService())->sales($d) as $s) fputcsv($o, [$s['receipt_no'], $s['cashier'], $s['subtotal'], $s['discount'], $s['vat'], $s['total'], $s['status'], $s['created_at']]);
        exit;
    }
    public function auditLog(): void {
        Auth::requireRole('Admin'); $a = new AuditLog();
        render('admin/audit_log', ['actions' => $a->actions(), 'logs' => $a->search($_GET['action'] ?? '', trim($_GET['q'] ?? ''))]);
    }
}
