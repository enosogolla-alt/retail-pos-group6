(function () {
    'use strict';

    var searchBox = document.getElementById('product-search');
    if (!searchBox) { return; } // not on the checkout page

    // TEMPORARY: a fixed total so we can test the payment dialog.
    // Task 3.3 will replace this with the real cart total.
    var cartTotal = 1250;

    // ---- Scanner focus (task 3.1) ----

    // Put the cursor back in the search box.
    function focusSearch() {
        searchBox.focus();
    }

    // When the cashier clicks elsewhere, return the cursor to the search box,
    // unless they clicked a field they need to type in, or are inside the dialog.
    document.addEventListener('click', function (event) {
        var tag = event.target.tagName;
        if (event.target.closest('dialog')) { return; }
        if (tag !== 'INPUT' && tag !== 'SELECT' && tag !== 'TEXTAREA') {
            focusSearch();
        }
    });

    // ---- Payment dialog (task 3.5) ----

    var dialog       = document.getElementById('payment-dialog');
    var payTotal     = document.getElementById('pay-total');
    var tabCash      = document.getElementById('tab-cash');
    var tabMpesa     = document.getElementById('tab-mpesa');
    var panelCash    = document.getElementById('panel-cash');
    var panelMpesa   = document.getElementById('panel-mpesa');
    var cashInput    = document.getElementById('cash-tendered');
    var cashChange   = document.getElementById('cash-change');
    var cashError    = document.getElementById('cash-error');
    var mpesaPhone   = document.getElementById('mpesa-phone');
    var mpesaCode    = document.getElementById('mpesa-code');
    var mpesaError   = document.getElementById('mpesa-error');
    var completeBtn  = document.getElementById('complete-sale');
    var cancelBtn    = document.getElementById('pay-cancel');
    var payCashBtn   = document.getElementById('pay-cash');
    var payMpesaBtn  = document.getElementById('pay-mpesa');

    var method = 'cash'; // which tab is open: 'cash' or 'mpesa'

    // Format a number as money, e.g. 1250 -> "1250.00".
    function money(n) {
        return Number(n).toFixed(2);
    }

    // Show one tab and hide the other.
    function showTab(which) {
        method = which;
        var isCash = (which === 'cash');
        panelCash.hidden  = !isCash;
        panelMpesa.hidden = isCash;
        tabCash.classList.toggle('is-active', isCash);
        tabMpesa.classList.toggle('is-active', !isCash);
        checkPayment();
        (isCash ? cashInput : mpesaPhone).focus();
    }

    // Cash: change = amount received - total. Valid only if it covers the total.
    function checkCash() {
        var tendered = parseFloat(cashInput.value);
        if (isNaN(tendered)) {
            cashChange.textContent = '0.00';
            cashError.hidden = true;
            return false;
        }
        if (tendered < cartTotal) {
            cashChange.textContent = '0.00';
            cashError.textContent = 'Amount received is less than the total.';
            cashError.hidden = false;
            return false;
        }
        cashChange.textContent = money(tendered - cartTotal);
        cashError.hidden = true;
        return true;
    }

    // M-Pesa: phone is 10 digits, code is exactly 10 letters or numbers.
    // (Whether the code was used before is checked by the server.)
    function checkMpesa() {
        var phoneOk = /^[0-9]{10}$/.test(mpesaPhone.value.trim());
        var codeOk  = /^[A-Za-z0-9]{10}$/.test(mpesaCode.value.trim());
        if (mpesaCode.value.length > 0 && !codeOk) {
            mpesaError.textContent = 'The M-Pesa code must be exactly 10 letters or numbers.';
            mpesaError.hidden = false;
        } else {
            mpesaError.hidden = true;
        }
        return phoneOk && codeOk;
    }

    // Complete sale stays disabled until the payment is valid.
    function checkPayment() {
        var valid = (method === 'cash') ? checkCash() : checkMpesa();
        completeBtn.disabled = !valid;
    }

    // Open the dialog on the chosen tab with empty fields.
    function openPayment(which) {
        payTotal.textContent = money(cartTotal);
        cashInput.value  = '';
        mpesaPhone.value = '';
        mpesaCode.value  = '';
        showTab(which);
        dialog.showModal();
    }

    payCashBtn.addEventListener('click',  function () { openPayment('cash'); });
    payMpesaBtn.addEventListener('click', function () { openPayment('mpesa'); });
    tabCash.addEventListener('click',     function () { showTab('cash'); });
    tabMpesa.addEventListener('click',    function () { showTab('mpesa'); });

    cashInput.addEventListener('input',  checkPayment);
    mpesaPhone.addEventListener('input', checkPayment);
    mpesaCode.addEventListener('input',  checkPayment);

    cancelBtn.addEventListener('click', function () { dialog.close(); });
    dialog.addEventListener('close', focusSearch); // also runs when Esc is pressed

    // TEMPORARY: enable the pay buttons for testing.
    // Later they will be enabled only when the cart has items.
    payCashBtn.disabled  = false;
    payMpesaBtn.disabled = false;

    focusSearch();
})();