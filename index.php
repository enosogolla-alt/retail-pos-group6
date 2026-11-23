<?php

declare(strict_types=1);

/**
 * Front controller. Every request enters here and is dispatched by the router.
 *
 * Owner: Kyle Tarus (M2)
 * Tasks: 2.3
 */

define('BASE_PATH', __DIR__);

require BASE_PATH . '/core/autoload.php';
require BASE_PATH . '/core/helpers.php';

if (!is_file(BASE_PATH . '/config/config.php')) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Configuration missing. Copy config/config.example.php to config/config.php and set your database details.';
    exit;
}

require BASE_PATH . '/config/database.php';

date_default_timezone_set((string) config('app.timezone', 'Africa/Nairobi'));
error_reporting(E_ALL);
ini_set('display_errors', config('app.debug', false) ? '1' : '0');

session_name('retailpos');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => basePath() === '' ? '/' : basePath() . '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

$router = new Router(basePath());

$router->get('/', static function (): void {
    if (!Auth::check()) {
        redirect('/login');
    }
    redirect(Auth::hasRole('Admin') ? '/admin/dashboard' : '/pos');
});

// Module 1 · Authentication & Inventory Control
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/admin/dashboard', [ProductController::class, 'dashboard']);
$router->get('/dashboard/alerts', [ProductController::class, 'alerts']);
$router->get('/admin/products', [ProductController::class, 'index']);
$router->post('/admin/products', [ProductController::class, 'create']);
$router->post('/admin/products/{id}', [ProductController::class, 'update']);
$router->post('/admin/products/{id}/toggle', [ProductController::class, 'toggle']);
$router->get('/admin/stock', [ProductController::class, 'stock']);
$router->post('/stock/adjust', [ProductController::class, 'adjustStock']);

$router->get('/admin/users', [UserController::class, 'index']);
$router->post('/admin/users', [UserController::class, 'create']);
$router->post('/admin/users/{id}', [UserController::class, 'update']);
$router->post('/admin/users/{id}/reset', [UserController::class, 'resetPassword']);
$router->post('/admin/users/{id}/unlock', [UserController::class, 'unlock']);
$router->post('/admin/users/{id}/toggle', [UserController::class, 'toggle']);

// Module 2 · Sales & Checkout Terminal
$router->get('/pos', [SaleController::class, 'checkout']);
$router->get('/products/lookup', [SaleController::class, 'findProduct']);
$router->post('/sales', [SaleController::class, 'createSale']);
$router->post('/mpesa/stk', [SaleController::class, 'sendMpesaPrompt']);
$router->get('/mpesa/status', [SaleController::class, 'mpesaStatus']);
$router->post('/mpesa/callback', [SaleController::class, 'mpesaCallback']);

// Module 3 · Stock Sync & Digital Receipts
$router->get('/receipts/{receiptNo}', [SaleController::class, 'receipt']);
$router->post('/sales/{id}/void', [SaleController::class, 'voidSale']);
$router->get('/admin/reports', [ReportController::class, 'daily']);
$router->get('/admin/reports/csv', [ReportController::class, 'exportCsv']);
$router->get('/admin/audit', [ReportController::class, 'auditLog']);
$router->get('/my-sales', [SaleController::class, 'history']);
$router->get('/admin/suppliers', [SupplierController::class, 'index']);
$router->post('/admin/suppliers', [SupplierController::class, 'create']);
$router->post('/admin/suppliers/{id}', [SupplierController::class, 'update']);
$router->post('/admin/suppliers/{id}/toggle', [SupplierController::class, 'toggle']);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

// Safaricom calls the callback server-to-server, so it cannot carry a session token.
if ($method === 'POST' && $router->path($uri) !== '/mpesa/callback') {
    Csrf::verify();
}

$router->dispatch($method, $uri);
