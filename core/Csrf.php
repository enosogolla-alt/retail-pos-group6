<?php
class Csrf {
    private const KEY = '_csrf_token';
    public static function token(): string {
        return $_SESSION[self::KEY] ??= bin2hex(random_bytes(32));
    }
    public static function field(): string {
        return '<input type="hidden" name="_csrf" value="' . e(self::token()) . '">';
    }
    public static function check(): void {
        $sent = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        $good = $_SESSION[self::KEY] ?? '';
        if ($good === '' || !is_string($sent) || !hash_equals($good, $sent)) {
            http_response_code(419);
            if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) json_out(['error' => 'Invalid or missing CSRF token.'], 419);
            exit('Invalid or missing CSRF token. Go back, refresh and try again.');
        }
    }
}
