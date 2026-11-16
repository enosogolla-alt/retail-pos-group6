<?php // Payment dialog (task 3.5): Cash tab and M-Pesa tab. Hidden until pos.js opens it. ?>
<dialog id="payment-dialog" class="pay-dialog">
    <div class="pay-dialog__head">
        <h2 class="pay-dialog__title">Payment</h2>
        <p class="pay-dialog__due">Total to pay: KES <strong id="pay-total">0.00</strong></p>
    </div>

    <div class="pay-tabs" role="tablist">
        <button type="button" id="tab-cash" class="pay-tabs__tab is-active" role="tab">Cash</button>
        <button type="button" id="tab-mpesa" class="pay-tabs__tab" role="tab">M-Pesa</button>
    </div>

    <!-- Cash -->
    <div id="panel-cash" class="pay-panel">
        <label for="cash-tendered">Amount received (KES)</label>
        <input type="number" id="cash-tendered" min="0" step="0.01" inputmode="decimal">
        <p class="pay-panel__line">Change: KES <strong id="cash-change">0.00</strong></p>
        <p id="cash-error" class="form-error" role="alert" hidden></p>
    </div>

    <!-- M-Pesa -->
    <div id="panel-mpesa" class="pay-panel" hidden>
        <label for="mpesa-phone">Customer phone number</label>
        <input type="tel" id="mpesa-phone" placeholder="07XXXXXXXX" inputmode="numeric">

        <label for="mpesa-code">M-Pesa code (10 characters)</label>
        <input type="text" id="mpesa-code" maxlength="10" autocomplete="off">
        <p id="mpesa-error" class="form-error" role="alert" hidden></p>
    </div>

    <div class="pay-dialog__actions">
        <button type="button" id="pay-cancel" class="btn btn--secondary">Cancel</button>
        <button type="button" id="complete-sale" class="btn btn--primary" disabled>Complete sale</button>
    </div>
</dialog>