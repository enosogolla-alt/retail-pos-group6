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
        $row = self::selectOne('SELECT setting_value FROM settings WHERE setting_key = ?', [$key]);

        return $row['setting_value'] ?? $default;
    }

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        $settings = [];
        foreach (self::select('SELECT setting_key, setting_value FROM settings') as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }
}
