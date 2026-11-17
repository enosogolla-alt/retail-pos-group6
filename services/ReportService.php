<?php
class ReportService {
    public function summary(string $date): array {
        return q("SELECT COALESCE(SUM(total),0) total, COUNT(*) t, COALESCE(SUM(vat),0) vat, COALESCE(AVG(total),0) avg_sale
                  FROM sales WHERE DATE(created_at)=? AND status='COMPLETED'", [$date])->fetch();
    }
    public function topProducts(string $date): array {
        return q("SELECT si.name, SUM(si.qty) qty, SUM(si.line_total) revenue FROM sale_items si JOIN sales s ON s.id=si.sale_id
                  WHERE DATE(s.created_at)=? AND s.status='COMPLETED' GROUP BY si.product_id, si.name ORDER BY qty DESC LIMIT 10", [$date])->fetchAll();
    }
    public function byCashier(string $date): array {
        return q("SELECT u.username, COUNT(*) txns, SUM(s.total) total FROM sales s JOIN users u ON u.id=s.cashier_id
                  WHERE DATE(s.created_at)=? AND s.status='COMPLETED' GROUP BY u.id, u.username ORDER BY total DESC", [$date])->fetchAll();
    }
    public function sales(string $date): array {
        return q('SELECT s.*, u.username AS cashier FROM sales s JOIN users u ON u.id=s.cashier_id WHERE DATE(s.created_at)=? ORDER BY s.id DESC', [$date])->fetchAll();
    }
    public function recent(int $n = 5): array {
        return q('SELECT s.receipt_no, s.total, u.username FROM sales s JOIN users u ON u.id=s.cashier_id ORDER BY s.id DESC LIMIT ' . (int)$n)->fetchAll();
    }
}
