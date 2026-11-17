<!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>Login · Retail POS</title>
<link rel="stylesheet" href="<?= e(base_url('assets/css/app.css')) ?>"></head>
<body class="auth-body">
<form method="post" action="<?= e(base_url('login')) ?>" class="auth-form">
    <h1>Retail POS</h1><p class="muted">Sign in to continue</p>
    <?php if ($m = flash('error')): ?><p class="error"><?= e($m) ?></p><?php endif; ?>
    <?= Csrf::field() ?>
    <label>Username <input type="text" name="username" required autofocus></label>
    <label>Password <input type="password" name="password" required></label>
    <button type="submit">Log in</button>
</form></body></html>
