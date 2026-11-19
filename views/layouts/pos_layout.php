<?php


$cashierName = $_SESSION['user']['full_name'] ?? 'Cashier';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Checkout') ?> - Retail POS</title>
    <link rel="stylesheet" href="<?= $baseUrl ?? '' ?>/assets/css/app.css">
</head>
<body class="pos-body">

<header class="pos-topbar">
    <div class="pos-topbar__brand">Retail POS</div>

    <div class="pos-topbar__user">
        <span class="pos-topbar__name"><?= e($cashierName) ?></span>

        <!-- Logout is a POST so the CSRF token is checked (Kyle's Csrf.php) -->
        <form action="/logout" method="post" class="pos-topbar__logout">
            <?= Csrf::field() ?>
            <button type="submit" class="btn btn--ghost">Log out</button>
        </form>
    </div>
</header>

<main class="pos-main">
    <?php require $viewFile; ?>
</main>

<script src="<?= $baseUrl ?? '' ?>/assets/js/pos.js"></script>
</body>
</html> 
<?php $u = Auth::user(); ?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>POS · Retail POS</title>
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <link rel="stylesheet" href="<?= e(base_url('assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(base_url('assets/css/pos-dialog.css')) ?>">
    <script>window.POS_BASE_URL = "<?= e(base_url()) ?>";</script>
</head>
<body class="pos-body">
<nav class="pos-nav"><strong>Retail POS</strong><span><?= e($u['name']) ?> · <?= e($u['role']) ?></span>
    <form method="post" action="<?= e(base_url('logout')) ?>" style="display:inline"><?= Csrf::field() ?><button type="submit">Logout</button></form>
</nav>
<main class="pos-main">
<?php if ($m = flash('error')): ?><p class="error"><?= e($m) ?></p><?php endif; ?>
<?= $content ?? '' ?>
</main>
<script src="<?= e(base_url('assets/js/pos.js')) ?>"></script>
</body></html>
