<?php $u = Auth::user(); ?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Admin · Retail POS</title>
<link rel="stylesheet" href="<?= e(base_url('assets/css/app.css')) ?>"></head>
<body class="admin">
<header class="admin-top"><strong>Retail POS</strong>
    <span class="user-chip"><?= e($u['name']) ?> · <?= e($u['role']) ?></span>
    <form method="post" action="<?= e(base_url('logout')) ?>" style="display:inline"><?= Csrf::field() ?><button type="submit" class="btn-logout">Logout</button></form>
</header>
<div class="admin-shell">
    <aside class="admin-side">
        <?php foreach (['dashboard'=>'Dashboard','products'=>'Products','stock'=>'Stock','users'=>'Users','reports'=>'Reports','audit'=>'Audit log'] as $p => $l): ?>
        <a href="<?= e(base_url("admin/$p")) ?>"><?= $l ?></a>
        <?php endforeach; ?>
    </aside>
    <main class="admin-content">
        <?php if ($m = flash('success')): ?><p class="ok"><?= e($m) ?></p><?php endif; ?>
        <?php if ($m = flash('error')):   ?><p class="error"><?= e($m) ?></p><?php endif; ?>
        <?= $content ?? '' ?>
    </main>
</div></body></html>
