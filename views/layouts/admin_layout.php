<?php /* Admin layout: top bar, side menu and messages · Owner: Xavier Biko (M1) · Task 1.2 */ ?>
<?php
$menu = [
    '/admin/dashboard' => 'Dashboard',
    '/admin/products' => 'Products',
    '/admin/stock' => 'Stock',
    '/admin/suppliers' => 'Suppliers',
    '/admin/users' => 'Users',
    '/admin/reports' => 'Sales reports',
    '/admin/audit' => 'Audit log',
];
$active ??= '';
$user = Auth::user();
$messages = takeFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Admin') ?> · Retail POS</title>
<link rel="icon" href="<?= e(asset('img/logo.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="admin-body">

<header class="topbar">
    <a class="topbar-brand" href="<?= e(url('/admin/dashboard')) ?>">
        <img src="<?= e(asset('img/logo.svg')) ?>" alt="" width="28" height="28">
        <span>Retail POS</span>
        <span class="topbar-section">Admin</span>
    </a>
    <?php if ($user !== null): ?>
        <div class="topbar-user">
            <span>Admin: <strong><?= e($user['full_name']) ?></strong></span>
            <form method="post" action="<?= e(url('/logout')) ?>">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-sm">Log out</button>
            </form>
        </div>
    <?php endif; ?>
</header>

<div class="admin-shell">
    <nav class="card sidenav" aria-label="Admin menu">
        <?php foreach ($menu as $href => $label): ?>
            <a class="sidenav-link<?= $href === $active ? ' is-active' : '' ?>" href="<?= e(url($href)) ?>"<?= $href === $active ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
        <?php endforeach; ?>
    </nav>

    <main class="admin-main">
        <?php if ($messages !== []): ?>
            <div class="flash-stack" role="status">
                <?php foreach ($messages as $message): ?>
                    <p class="flash flash-<?= e($message['type']) ?>"><?= e($message['message']) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
<?= $content ?>
    </main>
</div>

<script src="<?= e(asset('js/admin.js')) ?>"></script>
</body>
</html>
