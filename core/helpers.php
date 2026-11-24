<?php

declare(strict_types=1);

/**
 * Functions shared by every layer.
 *
 * Owner: Kyle Tarus (M2)
 * Tasks: 2.3
 */

function config(string $key, mixed $default = null): mixed
{
    static $config = null;
    $config ??= require BASE_PATH . '/config/config.php';

    $value = $config;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function kes(int|float|string $amount): string
{
    return 'KES ' . number_format((float) $amount, 2);
}

function money(int|float|string $amount): string
{
    return number_format((float) $amount, 2);
}

/**
 * URL prefix of the application folder, for example "/retail-pos-group6". Empty when served from the web root.
 */
function basePath(): string
{
    $directory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));

    return rtrim($directory, '/');
}

function url(string $path = '/'): string
{
    return basePath() . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function wantsJson(): bool
{
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    return str_contains($accept, 'application/json') || str_contains($contentType, 'application/json');
}

function json(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

/**
 * Decoded JSON request body, or an empty array when the body is not valid JSON.
 *
 * @return array<string, mixed>
 */
function jsonInput(): array
{
    $data = json_decode((string) file_get_contents('php://input'), true);

    return is_array($data) ? $data : [];
}

function input(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;

    return is_string($value) ? trim($value) : $default;
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function clientIp(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Returns pending flash messages once, then clears them.
 *
 * @return list<array{type: string, message: string}>
 */
function takeFlash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return $messages;
}

/**
 * Runs $work in a database transaction. A call made inside an open transaction uses a
 * savepoint instead, so services can call each other and tests can roll everything back.
 */
function transaction(callable $work): mixed
{
    static $depth = 0;

    $db = db();
    $outer = $depth === 0 && !$db->inTransaction();
    $savepoint = 'sp' . $depth;

    $outer ? $db->beginTransaction() : $db->exec('SAVEPOINT ' . $savepoint);
    $depth++;

    try {
        $result = $work($db);
        $depth--;
        $outer ? $db->commit() : $db->exec('RELEASE SAVEPOINT ' . $savepoint);

        return $result;
    } catch (Throwable $exception) {
        $depth--;
        $outer ? $db->rollBack() : $db->exec('ROLLBACK TO SAVEPOINT ' . $savepoint);

        throw $exception;
    }
}

/**
 * Renders views/{template}.php, optionally inside views/layouts/{layout}_layout.php.
 *
 * @param array<string, mixed> $data
 */
function view(string $template, array $data = [], ?string $layout = null): void
{
    extract($data, EXTR_SKIP);

    ob_start();
    require BASE_PATH . '/views/' . $template . '.php';
    $content = (string) ob_get_clean();

    if ($layout === null) {
        echo $content;
        return;
    }

    require BASE_PATH . '/views/layouts/' . $layout . '_layout.php';
}
