<?php

declare(strict_types=1);

/**
 * Sales reports, CSV export and the audit log.
 *
 * Owner: David Muturi (M6)
 * Interface: Andria Shikwe (M5)
 * Tasks: 5.4-5.6, 6.3, 6.5
 */
final class ReportController
{
    public function daily(): void
    {
        Auth::requireRole('Admin');

        $date = $this->date();
        $reports = new ReportService();

        view('admin/reports', [
            'title' => 'Sales reports',
            'active' => '/admin/reports',
            'date' => $date,
            'summary' => $reports->dailySummary($date),
            'topProducts' => $reports->topProducts($date),
            'byCashier' => $reports->salesByCashier($date),
            'sales' => $reports->sales($date),
        ], 'admin');
    }

    public function exportCsv(): void
    {
        Auth::requireRole('Admin');

        $date = $this->date();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="sales-' . $date . '.csv"');
        echo (new ReportService())->toCsv($date);
    }

    public function auditLog(): void
    {
        Auth::requireRole('Admin');

        $action = input('action');
        $term = input('q');
        $date = input('date');
        $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 ? $date : '';

        view('admin/audit_log', [
            'title' => 'Audit log',
            'active' => '/admin/audit',
            'entries' => AuditLog::search($action, $term, $date),
            'actions' => AuditLog::actions(),
            'filters' => ['action' => $action, 'q' => $term, 'date' => $date],
        ], 'admin');
    }

    private function date(): string
    {
        $date = input('date');

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 && strtotime($date) !== false ? $date : date('Y-m-d');
    }
}
