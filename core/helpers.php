<?php
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function config(string $k, $d = null) { static $c; $c ??= require __DIR__ . '/../config/config.php'; return $c[$k] ?? $d; }
function base_url(string $p = ''): string { return rtrim(config('base_url'), '/') . '/' . ltrim($p, '/'); }
function redirect(string $p): void { header('Location: ' . base_url($p)); exit; }   // always exits
function flash(string $k, ?string $m = null): ?string {
    if ($m !== null) { $_SESSION['_flash'][$k] = $m; return null; }
    $v = $_SESSION['_flash'][$k] ?? null; unset($_SESSION['_flash'][$k]); return $v;
}
function kes($n): string { return 'KES ' . number_format((float)$n, 2); }
function json_out($d, int $code = 200): void { http_response_code($code); header('Content-Type: application/json'); echo json_encode($d); exit; }
function json_in(): array { return json_decode(file_get_contents('php://input'), true) ?: []; }
function render(string $view, array $data = []): void { extract($data); require __DIR__ . "/../views/$view.php"; }
