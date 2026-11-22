<?php /* Layout for pages without navigation: sign-in, receipts, errors · Owner: Xavier Biko (M1) · Task 1.1 */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Retail POS') ?> · Retail POS</title>
<link rel="icon" href="<?= e(asset('img/logo.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="guest">
<main class="guest-main">
<?= $content ?>
</main>
</body>
</html>
