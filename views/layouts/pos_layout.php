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
