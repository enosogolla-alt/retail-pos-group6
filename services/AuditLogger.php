<?php

declare(strict_types=1);

/**
 * Writes the audit trail for sensitive actions.
 *
 * Owner: David Muturi (M6)
 * Tasks: 6.3
 */
final class AuditLogger
{
    /**
     * Call inside the same transaction as the action, so the entry and the change are saved together.
     */
    public static function log(?int $userId, string $action, string $details = ''): void
    {
        AuditLog::create($userId, $action, $details, clientIp());
    }
}
