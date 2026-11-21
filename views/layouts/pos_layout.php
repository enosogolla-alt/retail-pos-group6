<?php  ?>
<?php
$user = Auth::user();
$messages = takeFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Checkout') ?> · Retail POS</title>
    <link rel="icon" href="<?= e(asset('img/logo.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/pos.css')) ?>">
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
</head>
<body class="pos-body">

<header class="pos-topbar">
    <div class="pos-topbar-brand">
        <img src="<?= e(asset('img/logo.svg')) ?>" alt="" width="28" height="28">
        <span>Retail POS</span>
        <span class="pos-topbar-section"><?= e($section ?? 'Checkout') ?></span>
    </div>

    <?php if ($user !== null): ?>
        <div class="pos-topbar-user">
            <span><?= e($user['role']) ?>: <strong><?= e($user['full_name']) ?></strong></span>

            <nav class="pos-nav" aria-label="Cashier">
                <?php $current = $section ?? 'Checkout'; ?>
                <a class="btn btn-sm <?= $current === 'Checkout' ? 'is-current' : '' ?>" href="<?= e(url('/pos')) ?>"<?= $current === 'Checkout' ? ' aria-current="page"' : '' ?>>Checkout</a>
                <a class="btn btn-sm <?= $current === 'My sales' ? 'is-current' : '' ?>" href="<?= e(url('/my-sales')) ?>"<?= $current === 'My sales' ? ' aria-current="page"' : '' ?>>My sales</a>
            </nav>

            <?php /* A POST request, so the CSRF token is checked and a link cannot sign a user out. */ ?>
            <form action="<?= e(url('/logout')) ?>" method="post" class="pos-topbar-logout">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-sm">Log out</button>
            </form>
        </div>
    <?php endif; ?>
</header>

<main class="pos-main">
    <?php foreach ($messages as $message): ?>
        <p class="pos-flash pos-flash-<?= e($message['type']) ?>" role="status"><?= e($message['message']) ?></p>
    <?php endforeach; ?>
<?= $content ?>
</main>

<script src="<?= e(asset('js/pos.js')) ?>"></script>
</body>
</html>
