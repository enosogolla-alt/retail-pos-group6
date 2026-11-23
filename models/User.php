<?php

declare(strict_types=1);

/**
 * Table: users.
 *
 * Owner: Kyle Tarus (M2)
 * Tasks: 2.2, 2.8
 */
final class User extends Model
{
    private const COLUMNS = 'id, username, full_name, role, is_active, failed_attempts, locked_until, must_change_pw, created_at';

    public static function findById(int $id): ?array
    {
        return self::selectOne('SELECT ' . self::COLUMNS . ' FROM users WHERE id = ?', [$id]);
    }

    /**
     * Includes the password hash, so use only for sign-in checks.
     */
    public static function findByUsername(string $username): ?array
    {
        return self::selectOne('SELECT * FROM users WHERE username = ?', [$username]);
    }

    public static function all(): array
    {
        return self::select('SELECT ' . self::COLUMNS . ' FROM users ORDER BY is_active DESC, role, full_name');
    }

    public static function usernameExists(string $username): bool
    {
        return self::selectOne('SELECT id FROM users WHERE username = ?', [$username]) !== null;
    }

    public static function create(string $username, string $fullName, string $role, string $passwordHash): int
    {
        self::execute(
            'INSERT INTO users (username, full_name, role, password_hash, must_change_pw) VALUES (?, ?, ?, ?, 1)',
            [$username, $fullName, $role, $passwordHash]
        );

        return self::lastId();
    }

    public static function update(int $id, string $fullName, string $role): void
    {
        self::execute('UPDATE users SET full_name = ?, role = ? WHERE id = ?', [$fullName, $role, $id]);
    }

    public static function setActive(int $id, bool $active): void
    {
        self::execute('UPDATE users SET is_active = ? WHERE id = ?', [$active ? 1 : 0, $id]);
    }

    public static function setPassword(int $id, string $passwordHash, bool $mustChange): void
    {
        self::execute(
            'UPDATE users SET password_hash = ?, must_change_pw = ?, failed_attempts = 0, locked_until = NULL WHERE id = ?',
            [$passwordHash, $mustChange ? 1 : 0, $id]
        );
    }

    /**
     * Counts a failed sign-in and locks the account once the limit is reached.
     */
    public static function addFailedAttempt(int $id, int $maxAttempts, int $lockMinutes): int
    {
        self::execute(
            // locked_until is set first: MySQL applies SET assignments left to right,
            // so it must read failed_attempts before the increment.
            'UPDATE users
                SET locked_until = IF(failed_attempts + 1 >= ?, NOW() + INTERVAL ? MINUTE, locked_until),
                    failed_attempts = failed_attempts + 1
              WHERE id = ?',
            [$maxAttempts, $lockMinutes, $id]
        );

        return (int) self::selectOne('SELECT failed_attempts FROM users WHERE id = ?', [$id])['failed_attempts'];
    }

    public static function resetFailedAttempts(int $id): void
    {
        self::execute('UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?', [$id]);
    }

    public static function isLocked(array $user): bool
    {
        return $user['locked_until'] !== null && strtotime((string) $user['locked_until']) > time();
    }
}
