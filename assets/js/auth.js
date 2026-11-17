/*
 * Sign-in page behaviour: required-field checks, password visibility,
 * Caps Lock warning, test-account fill and double-submit prevention.
 * Credentials are verified on the server only.
 * Owner: Xavier Biko (M1)
 * Task: 1.1
 */
'use strict';

(function () {
    const form = document.getElementById('login-form');
    if (!form) {
        return;
    }

    const username = document.getElementById('username');
    const password = document.getElementById('password');
    const submit = document.getElementById('login-submit');
    const toggle = document.getElementById('toggle-password');
    const capsWarning = document.getElementById('caps-warning');

    const fields = [
        { input: username, error: document.getElementById('username-error') },
        { input: password, error: document.getElementById('password-error') },
    ];

    function setInvalid(field, invalid) {
        field.input.setAttribute('aria-invalid', invalid ? 'true' : 'false');
        field.error.hidden = !invalid;
    }

    form.addEventListener('submit', function (event) {
        let firstInvalid = null;

        fields.forEach(function (field) {
            const empty = field.input.value.trim() === '';
            setInvalid(field, empty);
            if (empty && !firstInvalid) {
                firstInvalid = field.input;
            }
        });

        if (firstInvalid) {
            event.preventDefault();
            firstInvalid.focus();
            return;
        }

        // Prevents a second request while the first is still being checked.
        submit.disabled = true;
        submit.textContent = 'Signing in…';
    });

    fields.forEach(function (field) {
        field.input.addEventListener('input', function () {
            if (field.input.value.trim() !== '') {
                setInvalid(field, false);
            }
        });
    });

    toggle.addEventListener('click', function () {
        const reveal = password.type === 'password';
        password.type = reveal ? 'text' : 'password';
        toggle.textContent = reveal ? 'Hide' : 'Show';
        toggle.setAttribute('aria-pressed', reveal ? 'true' : 'false');
        password.focus();
    });

    password.addEventListener('keyup', function (event) {
        capsWarning.hidden = !(event.getModifierState && event.getModifierState('CapsLock'));
    });

    password.addEventListener('blur', function () {
        capsWarning.hidden = true;
    });

    document.querySelectorAll('[data-username]').forEach(function (chip) {
        chip.addEventListener('click', function () {
            username.value = chip.dataset.username;
            password.value = chip.dataset.password;
            fields.forEach(function (field) {
                setInvalid(field, false);
            });
            submit.focus();
        });
    });
})();
