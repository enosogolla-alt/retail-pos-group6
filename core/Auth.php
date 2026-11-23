<?php

declare(strict_types=1);

/**
 * Session-based authentication and role checks.
 *
 * Owner: Kyle Tarus (M2)
 * Tasks: 2.2, 2.3
 */
final class Auth
{
    private const SESSION_KEY = 'user';

    /**
     * @return array{id: int, username: string, full_name: string, role: string}|null
     */
    public static function user(): ?array
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function hasRole(string ...$roles): bool
    {
        return self::check() && in_array(self::user()['role'], $roles, true);
    }

    /**
     * @param array{id: int|string, username: string, full_name: string, role: string} $user
     */
    public static function login(array $user): void
    {
        // A fresh session ID on sign-in prevents session fixation.
        session_regenerate_id(true);

        $_SESSION[self::SESSION_KEY] = [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'role' => $user['role'],
        ];
    }

    public static function logout(): void
    {
        $_SESSION = [];

        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?: 'Lax',
        ]);

        session_destroy();
    }

    public static function requireLogin(): void
    {
        if (self::check()) {
            return;
        }

        if (wantsJson()) {
            json(['error' => 'Your session has ended. Sign in again.'], 401);
        }

        redirect('/login');
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();

        if (self::hasRole(...$roles)) {
            return;
        }

        if (wantsJson()) {
            json(['error' => 'You do not have permission for this action.'], 403);
        }

        http_response_code(403);
        view('errors/403', ['title' => 'Access denied'], 'guest');
        exit;
    }
}
