<?php

declare(strict_types=1);

/**
 * Table: audit_log. Append-only: no update or delete methods by design.
 *
 * Owner: David Muturi (M6)
 * Tasks: 6.3
 */
final class AuditLog extends Model
{
    public static function create(?int $userId, string $action, string $details, string $ipAddress): void
    {
        throw new LogicException('Not implemented: task 6.3.');
    }

    public static function search(?string $action = null, ?string $term = null, ?string $date = null, int $limit = 200): array
    {
        throw new LogicException('Not implemented: task 6.3.');
    }
}
