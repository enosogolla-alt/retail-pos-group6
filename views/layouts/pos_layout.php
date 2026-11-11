<?php
// views/layouts/pos_layout.php  (Task 3.1 - Stacy)
// The frame around every cashier screen: dark top bar + page content.
// The controller sets $pageTitle and $viewFile, then includes this layout.
// e() is the safe-output helper from core/helpers.php.

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