<?php
class AuditLog {
    public function add(?int $uid, string $action, string $details): void {
        q('INSERT INTO audit_log(user_id,action,details,ip_address) VALUES (?,?,?,?)', [$uid, $action, $details, $_SERVER['REMOTE_ADDR'] ?? 'cli']);
    }
    public function actions(): array { return q('SELECT DISTINCT action FROM audit_log ORDER BY action')->fetchAll(PDO::FETCH_COLUMN); }
    public function search(string $action = '', string $text = ''): array {
        $w = []; $p = [];
        if ($action !== '') { $w[] = 'a.action=?'; $p[] = $action; }
        if ($text !== '')   { $w[] = '(u.username LIKE ? OR a.details LIKE ?)'; $p[] = "%$text%"; $p[] = "%$text%"; }
        return q('SELECT a.*, u.username FROM audit_log a LEFT JOIN users u ON u.id=a.user_id' . ($w ? ' WHERE ' . implode(' AND ', $w) : '') . ' ORDER BY a.id DESC LIMIT 200', $p)->fetchAll();
    }
}
