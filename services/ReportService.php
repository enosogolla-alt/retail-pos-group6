<?php

declare(strict_types=1);

/**
 * Daily sales figures and exports. Voided sales are excluded from every total.
 *
 * Owner: David Muturi (M6)
 * Tasks: 6.5
 */
final class ReportService
{
    /**
     * @return array{count: int, total: string, average: string, vat: string, cash_count: int, cash_total: string, mpesa_count: int, mpesa_total: string, voided: int}
     */
    public function dailySummary(string $date): array
    {
        $row = Sale::summaryForDate($date);
        $count = (int) $row['count'];

        return [
            'count' => $count,
            'total' => (string) $row['total'],
            'average' => $count > 0 ? number_format((float) $row['total'] / $count, 2, '.', '') : '0.00',
            'vat' => (string) $row['vat'],
            'cash_count' => (int) $row['cash_count'],
            'cash_total' => (string) $row['cash_total'],
            'mpesa_count' => (int) $row['mpesa_count'],
            'mpesa_total' => (string) $row['mpesa_total'],
            'voided' => (int) $row['voided'],
        ];
    }

    public function topProducts(string $date, int $limit = 5): array
    {
        return SaleItem::topProducts($date, $limit);
    }

    public function salesByCashier(string $date): array
    {
        return Sale::totalsByCashier($date);
    }

    public function sales(string $date): array
    {
        return Sale::forDate($date);
    }

    public function toCsv(string $date): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Receipt', 'Date', 'Time', 'Cashier', 'Method', 'Subtotal', 'Discount', 'Total', 'VAT', 'Status']);

        foreach (Sale::forDate($date) as $sale) {
            $created = strtotime((string) $sale['created_at']);
            fputcsv($handle, [
                $sale['receipt_no'],
                date('Y-m-d', $created),
                date('H:i', $created),
                $sale['cashier'],
                $sale['method'] === 'MPESA' ? 'M-Pesa' : 'Cash',
                $sale['subtotal'],
                $sale['discount'],
                $sale['total'],
                $sale['vat'],
                $sale['status'],
            ]);
        }

        rewind($handle);
        $csv = (string) stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
