<?php
session_start();
require __DIR__ . '/core/helpers.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/core/Csrf.php';
require __DIR__ . '/core/Auth.php';
require __DIR__ . '/core/Router.php';

spl_autoload_register(function ($class) {
    foreach (['models', 'controllers', 'services'] as $dir) {
        $f = __DIR__ . "/$dir/$class.php";
        if (file_exists($f)) { require $f; return; }
    }
});

// CSRF on every POST except the Safaricom callback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'], '/mpesa/callback') === false) Csrf::check();

$r = new Router();
// Public
$r->get('/login',   fn() => (new AuthController())->showLogin());
$r->post('/login',  fn() => (new AuthController())->login());
$r->post('/logout', fn() => (new AuthController())->logout());
$r->get('/',        fn() => redirect(Auth::check() ? (Auth::role() === 'Admin' ? 'admin/dashboard' : 'pos') : 'login'));
// Admin: dashboard, products, stock
$r->get('/admin/dashboard',  fn() => (new ProductController())->dashboard());
$r->get('/dashboard/alerts', fn() => (new ProductController())->alerts());
$r->get('/admin/products',              fn() => (new ProductController())->index());
$r->post('/admin/products',             fn() => (new ProductController())->create());
$r->post('/admin/products/{id}',        fn($id) => (new ProductController())->update($id));
$r->post('/admin/products/{id}/toggle', fn($id) => (new ProductController())->deactivate($id));
$r->get('/admin/stock',   fn() => (new ProductController())->stock());
$r->post('/stock/adjust', fn() => (new ProductController())->adjustStock());
// Admin: users
$r->get('/admin/users',                 fn() => (new UserController())->index());
$r->post('/admin/users',                fn() => (new UserController())->create());
$r->post('/admin/users/{id}',           fn($id) => (new UserController())->update($id));
$r->post('/admin/users/{id}/reset',     fn($id) => (new UserController())->resetPassword($id));
$r->post('/admin/users/{id}/unlock',    fn($id) => (new UserController())->unlock($id));
$r->post('/admin/users/{id}/toggle',    fn($id) => (new UserController())->toggle($id));
// POS (Winston)
$r->get('/pos',             fn() => (new SaleController())->checkout());
$r->get('/products/lookup', fn() => (new SaleController())->findProduct($_GET['code'] ?? ''));
$r->post('/sales',          fn() => (new SaleController())->createSale());
$r->post('/mpesa/stk',      fn() => (new SaleController())->sendMpesaPrompt());
$r->get('/mpesa/status',    fn() => (new SaleController())->mpesaStatus());
$r->post('/mpesa/callback', fn() => (new SaleController())->mpesaCallback());
// Receipts + void (David)
$r->get('/receipts/{no}',      fn($no) => (new ReceiptController())->receipt($no));
$r->get('/receipts/{no}/copy', fn($no) => (new ReceiptController())->reprint($no));
$r->post('/sales/{id}/void',   fn($id) => (new ReceiptController())->voidSale($id));
// Reports + audit (David)
$r->get('/admin/reports',     fn() => (new ReportController())->daily());
$r->get('/admin/reports/csv', fn() => (new ReportController())->exportCsv());
$r->get('/admin/audit',       fn() => (new ReportController())->auditLog());

$r->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
