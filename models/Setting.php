<?php

declare(strict_types=1);

/**
 * Table: settings. Shop details printed on receipts.
 *
 * Owner: David Muturi (M6)
 * Tasks: 6.2
 */
final class Setting extends Model
{
    public static function get(string $key, ?string $default = null): ?string
    {
        throw new LogicException('Not implemented: task 6.2.');
    }

    public static function all(): array
    {
        throw new LogicException('Not implemented: task 6.2.');
    }
}
