<?php

declare(strict_types=1);

/**
 * Table: audit_log. Append-only: there are no update or delete methods by design.
 *
 * Owner: David Muturi (M6)
 * Tasks: 6.3
 */
final class AuditLog extends Model
{
    public static function create(?int $userId, string $action, string $details, string $ipAddress): void
    {
        self::execute(
            'INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)',
            [$userId, $action, mb_substr($details, 0, 500), $ipAddress]
        );
    }

    public static function search(?string $action = null, ?string $term = null, ?string $date = null, int $limit = 300): array
    {
        $limit = max(1, min($limit, 1000));
        $where = [];
        $params = [];

        if ($action !== null && $action !== '') {
            $where[] = 'a.action = ?';
            $params[] = $action;
        }
        if ($term !== null && $term !== '') {
            $where[] = '(a.details LIKE ? OR u.full_name LIKE ? OR u.username LIKE ?)';
            array_push($params, '%' . $term . '%', '%' . $term . '%', '%' . $term . '%');
        }
        if ($date !== null && $date !== '') {
            $where[] = 'DATE(a.created_at) = ?';
            $params[] = $date;
        }

        return self::select(
            'SELECT a.*, COALESCE(u.full_name, \'Unknown user\') AS user_name
               FROM audit_log a LEFT JOIN users u ON u.id = a.user_id
             ' . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . '
              ORDER BY a.created_at DESC, a.id DESC
              LIMIT ' . $limit,
            $params
        );
    }

    public static function actions(): array
    {
        return array_column(self::select('SELECT DISTINCT action FROM audit_log ORDER BY action'), 'action');
    }
}
