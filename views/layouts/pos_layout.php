<?php /* Checkout layout: dark top bar and page content · Owner: Stacy Kwamboka (M3) · Task 3.1 */ ?>
<?php $user = Auth::user(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Checkout') ?> · Retail POS</title>
    <link rel="icon" href="<?= e(base_url('assets/img/logo.svg')) ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?= e(base_url('assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(base_url('assets/css/pos.css')) ?>">
</head>
<body class="pos-body">

<header class="pos-topbar">
    <div class="pos-topbar-brand">
        <img src="<?= e(base_url('assets/img/logo.svg')) ?>" alt="" width="28" height="28">
        <span>Retail POS</span>
        <span class="pos-topbar-section">Checkout</span>
    </div>

    <?php if ($user !== null): ?>
        <div class="pos-topbar-user">
            <span><?= e($user['role']) ?>: <strong><?= e($user['name']) ?></strong></span>

            <?php if ($user['role'] === 'Admin'): ?>
                <a class="btn btn-sm" href="<?= e(base_url('admin/dashboard')) ?>">Admin panel</a>
            <?php endif; ?>

            <?php /* A POST request, so the CSRF token is checked and a link cannot sign a user out. */ ?>
            <form action="<?= e(base_url('logout')) ?>" method="post" class="pos-topbar-logout">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-sm">Log out</button>
            </form>
        </div>
    <?php endif; ?>
</header>

<main class="pos-main">
<?= $content ?>
</main>

<script src="<?= e(base_url('assets/js/pos.js')) ?>"></script>
</body>
</html>
