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
(() => {
const BASE = window.POS_BASE_URL;
const cart = [];
let discountPct = 0;
let currentPayment = null;   // {method, amount, mpesa_code}
let polling = 0;             // token so an old poll stops when the dialog closes

const $ = (id) => document.getElementById(id);
const fmt = (n) => 'KES ' + Number(n).toFixed(2);
const esc = (s) => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
const post = (path, body) => fetch(BASE + path, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() }, body: JSON.stringify(body) }).then(r => r.json());

// Prices are VAT-inclusive: VAT is extracted, not added.
function totals() {
    const subtotal = cart.reduce((s, l) => s + l.price * l.qty, 0);
    const discount = subtotal * (discountPct / 100);
    const total = subtotal - discount;
    return { subtotal, discount, vat: total - total / 1.16, total };
}

async function lookup(code) {
    const res = await fetch(`${BASE}products/lookup?code=${encodeURIComponent(code)}`);
    return res.ok ? res.json() : null;
}
function addToCart(p) {
    const id = Number(p.id), line = cart.find(l => l.id === id);
    if (line) line.qty++; else cart.push({ id, name: p.name, price: Number(p.selling_price), qty: 1 });
    render();
}
function changeQty(id, d) {
    const line = cart.find(l => l.id === id); if (!line) return;
    line.qty += d; if (line.qty <= 0) cart.splice(cart.indexOf(line), 1);
    render();
}
function removeLine(id) { const i = cart.findIndex(l => l.id === id); if (i >= 0) cart.splice(i, 1); render(); }

function render() {
    $('cart-body').innerHTML = cart.length ? cart.map(l => `
        <tr><td>${esc(l.name)}</td><td>${fmt(l.price)}</td>
        <td><button type="button" class="qty-btn" data-id="${l.id}" data-delta="-1">−</button> ${l.qty}
            <button type="button" class="qty-btn" data-id="${l.id}" data-delta="1">+</button></td>
        <td>${fmt(l.price * l.qty)}</td>
        <td><button type="button" class="remove-btn" data-id="${l.id}">×</button></td></tr>`).join('')
        : '<tr><td colspan="5" class="empty">Cart is empty</td></tr>';
    const t = totals();
    $('sum-subtotal').textContent = fmt(t.subtotal); $('sum-discount').textContent = fmt(t.discount);
    $('sum-vat').textContent = fmt(t.vat); $('sum-total').textContent = fmt(t.total);
}

// ---- payment dialog (inline, cart is preserved) ----
function showTab(method) {
    polling++;
    currentPayment = { method };
    $('complete-sale').disabled = true;
    document.querySelectorAll('.tab').forEach(t => t.classList.toggle('active', t.dataset.tab === method.toLowerCase()));
    $('tab-cash').style.display = method === 'CASH' ? 'block' : 'none';
    $('tab-mpesa').style.display = method === 'MPESA' ? 'block' : 'none';
    if (method === 'CASH') updateCash();
}
function openPayment(method) {
    if (!cart.length) return alert('Cart is empty');
    $('pay-total').textContent = fmt(totals().total);
    $('cash-amount').value = ''; $('cash-change').textContent = fmt(0);
    $('mpesa-phone').value = ''; $('mpesa-status').textContent = 'Waiting…';
    $('payment-dialog').style.display = 'flex';
    showTab(method);
}
function closePayment() { polling++; $('payment-dialog').style.display = 'none'; currentPayment = null; }
function updateCash() {
    const total = totals().total, cash = Number($('cash-amount').value) || 0;
    $('cash-change').textContent = fmt(Math.max(cash - total, 0));
    if (currentPayment?.method === 'CASH') { currentPayment.amount = total; $('complete-sale').disabled = cash < total; }
}
async function sendMpesaPrompt() {
    const phone = $('mpesa-phone').value.trim();
    if (!/^(\+?254|0)[17]\d{8}$/.test(phone)) return alert('Enter a valid Safaricom number, e.g. 2547XXXXXXXX');
    const amount = Math.ceil(totals().total);          // STK takes whole shillings; server accepts amount >= total
    const data = await post('mpesa/stk', { phone, amount });
    if (data.error) return alert(data.error);
    currentPayment.amount = amount;
    $('mpesa-status').textContent = 'Prompt sent — waiting for customer…';
    pollMpesa(data.CheckoutRequestID);
}
function pollMpesa(id) {
    const my = ++polling; let tries = 0;
    const tick = async () => {
        if (my !== polling) return;
        const data = await (await fetch(`${BASE}mpesa/status?id=${encodeURIComponent(id)}`)).json();
        if (my !== polling) return;
        if (data.status === 'paid') {
            $('mpesa-status').textContent = 'Paid: ' + data.mpesa_code;
            currentPayment.mpesa_code = data.mpesa_code; $('complete-sale').disabled = false; return;
        }
        if (data.status === 'failed') { $('mpesa-status').textContent = 'Payment failed or cancelled.'; return; }
        if (++tries > 40) { $('mpesa-status').textContent = 'Timed out. Try sending the prompt again.'; return; }
        setTimeout(tick, 3000);
    };
    setTimeout(tick, 3000);
}
async function completeSale() {
    if (!currentPayment) return;
    $('complete-sale').disabled = true;
    const data = await post('sales', { cart: cart.map(l => ({ product_id: l.id, qty: l.qty })), discount_pct: discountPct, payment: currentPayment });
    if (data.error) { $('complete-sale').disabled = false; return alert(data.error); }
    window.location.href = `${BASE}receipts/${data.receipt_no}`;
}

document.addEventListener('DOMContentLoaded', () => {
    if (!$('cart-body')) return;
    $('search').addEventListener('keydown', async (e) => {
        if (e.key !== 'Enter') return;
        const q = e.target.value.trim(); if (!q) return;
        const p = await lookup(q);
        if (p && p.id) { addToCart(p); e.target.value = ''; } else alert('Product not found');
    });
    $('cart-body').addEventListener('click', (e) => {
        const id = Number(e.target.dataset.id); if (!id) return;
        if (e.target.classList.contains('qty-btn')) changeQty(id, Number(e.target.dataset.delta));
        if (e.target.classList.contains('remove-btn')) removeLine(id);
    });
    $('discount-input').addEventListener('input', (e) => { discountPct = Math.min(10, Math.max(0, Number(e.target.value) || 0)); render(); });
    $('pay-cash').addEventListener('click', () => openPayment('CASH'));
    $('pay-mpesa').addEventListener('click', () => openPayment('MPESA'));
    $('cash-amount').addEventListener('input', updateCash);       // attached once
    document.querySelectorAll('.tab').forEach(t => t.addEventListener('click', () => showTab(t.dataset.tab.toUpperCase())));
    $('mpesa-send').addEventListener('click', sendMpesaPrompt);
    $('complete-sale').addEventListener('click', completeSale);
    $('cancel-payment').addEventListener('click', closePayment);
});
})();
