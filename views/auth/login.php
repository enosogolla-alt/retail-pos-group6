<?php /* Sign-in page · Owner: Xavier Biko (M1) · Task 1.1 · Prototype: Login */ ?>
<?php
/**
 * Variables supplied by AuthController:
 * @var string|null $error    Message to show after a failed attempt.
 * @var string|null $username Username to keep in the field after a failed attempt.
 */
?>
<div class="login-shell">
<aside class="login-aside">
    <div class="brand-mark">
        <img src="<?= e(asset('img/logo.svg')) ?>" alt="" width="40" height="40">
        <span>Retail POS</span>
    </div>

    <div>
        <p class="login-headline">Every sale, shelf and supplier in one place.</p>
        <p class="login-lede">Cashiers ring up sales with cash or M-Pesa. Managers keep stock, staff and suppliers in order.</p>
    </div>

    <ul class="login-points">
        <li>Receipts with VAT worked out for you</li>
        <li>Low-stock alerts before shelves run empty</li>
        <li>A full record of who changed what</li>
    </ul>
</aside>

<section class="card guest-card" aria-labelledby="login-title">
    <div class="login-intro">
        <h1 class="page-title" id="login-title">Sign in</h1>
        <p class="muted">Use the username and password from your manager. Your role decides which screen opens next.</p>
    </div>

    <?php if (!empty($error)): ?>
        <p class="alert alert-error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <form class="form" id="login-form" method="post" action="<?= e(url('/login')) ?>" novalidate>
        <?= Csrf::field() ?>

        <div class="form-group">
            <label class="label" for="username">Username</label>
            <input
                class="field"
                id="username"
                name="username"
                type="text"
                autocomplete="username"
                autocapitalize="none"
                spellcheck="false"
                value="<?= e($username ?? '') ?>"
                aria-describedby="username-error"
                required
                autofocus
            >
            <p class="field-error" id="username-error" hidden>Enter your username.</p>
        </div>

        <div class="form-group">
            <label class="label" for="password">Password</label>
            <div class="input-group">
                <input
                    class="field"
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    aria-describedby="password-error caps-warning"
                    required
                >
                <button type="button" class="input-action" id="toggle-password" aria-controls="password" aria-pressed="false">Show</button>
            </div>
            <p class="field-error" id="password-error" hidden>Enter your password.</p>
            <p class="field-hint" id="caps-warning" hidden>Caps Lock is on.</p>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" id="login-submit">Log in</button>
    </form>
</section>
</div>

<script src="<?= e(asset('js/auth.js')) ?>" defer></script>
