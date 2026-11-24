<?php

declare(strict_types=1);

/**
 * Table: sale_items. Stores the unit price at the time of sale, so later price changes never alter old sales (BR-3).
 *
 * Owner: Winston Enos (M4)
 * Tasks: 4.5 · report query used by ReportService (6.5)
 */
final class SaleItem extends Model
{
    /**
     * @param list<array{product_id: int, qty: int, unit_price: string, line_total: string}> $items
     */
    public static function createMany(int $saleId, array $items): void
    {
        foreach ($items as $item) {
            self::execute(
                'INSERT INTO sale_items (sale_id, product_id, qty, unit_price, line_total) VALUES (?, ?, ?, ?, ?)',
                [$saleId, $item['product_id'], $item['qty'], $item['unit_price'], $item['line_total']]
            );
        }
    }

    public static function forSale(int $saleId): array
    {
        return self::select(
            'SELECT i.*, p.name, p.barcode FROM sale_items i JOIN products p ON p.id = i.product_id WHERE i.sale_id = ? ORDER BY i.id',
            [$saleId]
        );
    }

    /**
     * Best sellers by quantity for one day, excluding voided sales.
     */
    public static function topProducts(string $date, int $limit = 5): array
    {
        $limit = max(1, min($limit, 20));

        return self::select(
            "SELECT p.name, SUM(i.qty) AS qty, SUM(i.line_total) AS revenue
               FROM sale_items i
               JOIN sales s ON s.id = i.sale_id
               JOIN products p ON p.id = i.product_id
              WHERE DATE(s.created_at) = ? AND s.status = 'COMPLETED'
              GROUP BY p.id, p.name
              ORDER BY qty DESC, revenue DESC
              LIMIT " . $limit,
            [$date]
        );
    }
}
