<?php

declare(strict_types=1);

/**
 * Table: sales.
 *
 * Owner: Winston Enos (M4)
 * Tasks: 4.5, 4.6, 4.9 · report queries used by ReportService (6.5)
 */
final class Sale extends Model
{
    private const SELECT = 'SELECT s.*, u.full_name AS cashier, p.method, p.amount_tendered, p.change_given, p.mpesa_code
                              FROM sales s
                              JOIN users u ON u.id = s.user_id
                              LEFT JOIN payments p ON p.sale_id = s.id';

    /**
     * @param array{receipt_no: string, user_id: int, subtotal: string, discount_percent: string, discount: string, total: string, vat: string} $data
     */
    public static function create(array $data): int
    {
        self::execute(
            'INSERT INTO sales (receipt_no, user_id, subtotal, discount_percent, discount, total, vat) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$data['receipt_no'], $data['user_id'], $data['subtotal'], $data['discount_percent'], $data['discount'], $data['total'], $data['vat']]
        );

        return self::lastId();
    }

    public static function findById(int $id): ?array
    {
        return self::selectOne(self::SELECT . ' WHERE s.id = ?', [$id]);
    }

    public static function findByReceiptNo(string $receiptNo): ?array
    {
        return self::selectOne(self::SELECT . ' WHERE s.receipt_no = ?', [$receiptNo]);
    }

    public static function forDate(string $date): array
    {
        return self::select(self::SELECT . ' WHERE DATE(s.created_at) = ? ORDER BY s.created_at DESC', [$date]);
    }

    /**
     * One cashier's sales for a day, newest first, with the number of items in each.
     */
    public static function forCashier(int $userId, string $date): array
    {
        return self::select(
            str_replace(
                'SELECT s.*,',
                'SELECT s.*, (SELECT SUM(i.qty) FROM sale_items i WHERE i.sale_id = s.id) AS items,',
                self::SELECT
            ) . ' WHERE s.user_id = ? AND DATE(s.created_at) = ? ORDER BY s.created_at DESC',
            [$userId, $date]
        );
    }

    public static function recent(int $limit = 5): array
    {
        $limit = max(1, min($limit, 50));

        return self::select(self::SELECT . ' ORDER BY s.created_at DESC LIMIT ' . $limit);
    }

    /**
     * Next number for today in the format RCP-YYYYMMDD-0001. Call inside the checkout
     * transaction: the row lock stops two tills taking the same number.
     */
    public static function nextReceiptNo(): string
    {
        $prefix = 'RCP-' . date('Ymd') . '-';
        $row = self::selectOne(
            'SELECT receipt_no FROM sales WHERE receipt_no LIKE ? ORDER BY receipt_no DESC LIMIT 1 FOR UPDATE',
            [$prefix . '%']
        );
        $last = $row !== null ? (int) substr($row['receipt_no'], -4) : 0;

        return $prefix . str_pad((string) ($last + 1), 4, '0', STR_PAD_LEFT);
    }

    public static function markVoid(int $id, string $reason, int $voidedBy): void
    {
        self::execute(
            "UPDATE sales SET status = 'VOID', void_reason = ?, voided_by = ?, voided_at = NOW() WHERE id = ? AND status = 'COMPLETED'",
            [$reason, $voidedBy, $id]
        );
    }

    /**
     * Completed-sale totals for one day, split by payment method; for one cashier when a user id is given.
     */
    public static function summaryForDate(string $date, ?int $userId = null): array
    {
        return self::selectOne(
            "SELECT COUNT(CASE WHEN s.status = 'COMPLETED' THEN 1 END) AS count,
                    COALESCE(SUM(CASE WHEN s.status = 'COMPLETED' THEN s.total END), 0) AS total,
                    COALESCE(SUM(CASE WHEN s.status = 'COMPLETED' THEN s.vat END), 0) AS vat,
                    COUNT(CASE WHEN s.status = 'COMPLETED' AND p.method = 'CASH' THEN 1 END) AS cash_count,
                    COALESCE(SUM(CASE WHEN s.status = 'COMPLETED' AND p.method = 'CASH' THEN s.total END), 0) AS cash_total,
                    COUNT(CASE WHEN s.status = 'COMPLETED' AND p.method = 'MPESA' THEN 1 END) AS mpesa_count,
                    COALESCE(SUM(CASE WHEN s.status = 'COMPLETED' AND p.method = 'MPESA' THEN s.total END), 0) AS mpesa_total,
                    COUNT(CASE WHEN s.status = 'VOID' THEN 1 END) AS voided
               FROM sales s
               LEFT JOIN payments p ON p.sale_id = s.id
              WHERE DATE(s.created_at) = ?" . ($userId !== null ? ' AND s.user_id = ?' : ''),
            $userId !== null ? [$date, $userId] : [$date]
        );
    }

    public static function totalsByCashier(string $date): array
    {
        return self::select(
            "SELECT u.full_name AS cashier, COUNT(*) AS sales, SUM(s.total) AS total
               FROM sales s JOIN users u ON u.id = s.user_id
              WHERE DATE(s.created_at) = ? AND s.status = 'COMPLETED'
              GROUP BY u.id, u.full_name
              ORDER BY total DESC",
            [$date]
        );
    }
}
