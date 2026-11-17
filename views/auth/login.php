<?php /* Sign-in page · Owner: Xavier Biko (M1) · Task 1.1 · Prototype: Login */ ?>
<?php
/**
 * Variables supplied by AuthController:
 * @var string|null $error    Message to show after a failed attempt.
 * @var string|null $username Username to keep in the field after a failed attempt.
 */
?>
<section class="card guest-card" aria-labelledby="login-title">
    <div class="brand-mark">
        <img src="<?= e(asset('img/logo.svg')) ?>" alt="" width="40" height="40">
        <span>Retail POS</span>
    </div>

    <div class="login-intro">
        <h1 class="page-title" id="login-title">Sign in</h1>
        <p class="muted">Your role decides which screen opens next.</p>
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

    <?php if (config('app.debug')): ?>
        <div class="login-footer">
            <p class="login-footer-title">Test accounts</p>
            <div class="chip-row">
                <button type="button" class="chip" data-username="cashier" data-password="1234">Cashier · cashier / 1234</button>
                <button type="button" class="chip" data-username="admin" data-password="admin123">Admin · admin / admin123</button>
            </div>
        </div>
    <?php endif; ?>
</section>

<script src="<?= e(asset('js/auth.js')) ?>" defer></script>
