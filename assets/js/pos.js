/*
 * Checkout screen behaviour: scanner focus and payment dialog.
 * Totals shown here are for display; the server recalculates them when the sale is saved.
 * Owner: Stacy Kwamboka (M3)
 * Tasks: 3.1, 3.5
 */
'use strict';

(function () {
    const searchBox = document.getElementById('product-search');
    if (!searchBox) {
        return;
    }

    // Live cart total; stays 0 until the cart (tasks 3.2-3.3) adds items.
    let cartTotal = 0;

    const dialog = document.getElementById('payment-dialog');
    const payTotal = document.getElementById('pay-total');
    const summaryTotal = document.getElementById('summary-total');
    const tabCash = document.getElementById('tab-cash');
    const tabMpesa = document.getElementById('tab-mpesa');
    const panelCash = document.getElementById('panel-cash');
    const panelMpesa = document.getElementById('panel-mpesa');
    const cashInput = document.getElementById('cash-tendered');
    const cashChange = document.getElementById('cash-change');
    const cashError = document.getElementById('cash-error');
    const mpesaPhone = document.getElementById('mpesa-phone');
    const mpesaCode = document.getElementById('mpesa-code');
    const mpesaError = document.getElementById('mpesa-error');
    const completeButton = document.getElementById('complete-sale');
    const cancelButton = document.getElementById('pay-cancel');
    const payCashButton = document.getElementById('pay-cash');
    const payMpesaButton = document.getElementById('pay-mpesa');

    let method = 'cash';

    function money(amount) {
        return Number(amount).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function focusSearch() {
        searchBox.focus();
    }

    // A barcode scanner types into whatever has focus, so focus returns to the
    // search box after any click that is not on a field or inside the dialog.
    document.addEventListener('click', function (event) {
        if (event.target.closest('dialog')) {
            return;
        }
        if (!['INPUT', 'SELECT', 'TEXTAREA'].includes(event.target.tagName)) {
            focusSearch();
        }
    });

    function showTab(which) {
        method = which;
        const isCash = which === 'cash';

        panelCash.hidden = !isCash;
        panelMpesa.hidden = isCash;
        tabCash.classList.toggle('is-active', isCash);
        tabMpesa.classList.toggle('is-active', !isCash);
        tabCash.setAttribute('aria-selected', String(isCash));
        tabMpesa.setAttribute('aria-selected', String(!isCash));

        checkPayment();
        (isCash ? cashInput : mpesaPhone).focus();
    }

    function checkCash() {
        const tendered = parseFloat(cashInput.value);

        if (Number.isNaN(tendered)) {
            cashChange.textContent = money(0);
            cashError.hidden = true;
            return false;
        }

        if (tendered < cartTotal) {
            cashChange.textContent = money(0);
            cashError.textContent = 'Amount received is less than the total.';
            cashError.hidden = false;
            return false;
        }

        cashChange.textContent = money(tendered - cartTotal);
        cashError.hidden = true;
        return true;
    }

    // Format checks only; whether a code has already been used is checked by the server.
    function checkMpesa() {
        const phoneOk = /^0[17]\d{8}$/.test(mpesaPhone.value.trim());
        const codeOk = /^[A-Za-z0-9]{10}$/.test(mpesaCode.value.trim());

        if (mpesaCode.value.length > 0 && !codeOk) {
            mpesaError.textContent = 'The M-Pesa code must be exactly 10 letters or numbers.';
            mpesaError.hidden = false;
        } else {
            mpesaError.hidden = true;
        }

        return phoneOk && codeOk;
    }

    function checkPayment() {
        const valid = method === 'cash' ? checkCash() : checkMpesa();
        completeButton.disabled = !valid;
    }

    function openPayment(which) {
        payTotal.textContent = money(cartTotal);
        cashInput.value = '';
        mpesaPhone.value = '';
        mpesaCode.value = '';
        showTab(which);
        dialog.showModal();
    }

    payCashButton.addEventListener('click', function () {
        openPayment('cash');
    });
    payMpesaButton.addEventListener('click', function () {
        openPayment('mpesa');
    });
    tabCash.addEventListener('click', function () {
        showTab('cash');
    });
    tabMpesa.addEventListener('click', function () {
        showTab('mpesa');
    });

    cashInput.addEventListener('input', checkPayment);
    mpesaPhone.addEventListener('input', checkPayment);
    mpesaCode.addEventListener('input', checkPayment);

    cancelButton.addEventListener('click', function () {
        dialog.close();
    });

    // Fires for Cancel and for the Esc key.
    dialog.addEventListener('close', focusSearch);

    // Quick add puts the product name in the search box, ready to add with Enter.
    document.querySelectorAll('[data-quick-add]').forEach(function (chip) {
        chip.addEventListener('click', function () {
            searchBox.value = chip.dataset.quickAdd;
        });
    });

    summaryTotal.textContent = money(cartTotal);

    focusSearch();
})();
