<?php

declare(strict_types=1);

/**
 * Cross-site request forgery protection for forms and fetch requests.
 *
 * Owner: Kyle Tarus (M2)
 * Tasks: 2.3
 */
final class Csrf
{
    private const KEY = 'csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::KEY];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="' . self::KEY . '" value="' . e(self::token()) . '">';
    }

    /**
     * Accepts the token from a form field or, for fetch requests, the X-CSRF-Token header.
     */
    public static function verify(): void
    {
        $sent = $_POST[self::KEY] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $expected = $_SESSION[self::KEY] ?? '';

        if (is_string($sent) && $expected !== '' && hash_equals($expected, $sent)) {
            return;
        }

        if (wantsJson()) {
            json(['error' => 'Your session has expired. Reload the page and try again.'], 419);
        }

        http_response_code(419);
        view('errors/419', ['title' => 'Session expired'], 'guest');
        exit;
    }
}
