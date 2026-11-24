<?php /* Payment dialog, opened by pos.js from the checkout screen · Owner: Stacy Kwamboka (M3) · Task 3.5 · Prototype: Payment */ ?>
<dialog id="payment-dialog" class="pay-dialog" aria-labelledby="pay-title">
    <div class="pay-dialog-head">
        <h2 class="pay-dialog-title" id="pay-title">Payment</h2>
    </div>

    <div class="pay-tabs" role="tablist" aria-label="Payment method">
        <button type="button" id="tab-cash" class="pay-tab is-active" role="tab" aria-selected="true" aria-controls="panel-cash">Cash</button>
        <button type="button" id="tab-mpesa" class="pay-tab" role="tab" aria-selected="false" aria-controls="panel-mpesa">M-Pesa</button>
    </div>

    <div class="pay-body">
        <div class="pay-due">
            <span>Total due</span>
            <strong class="pay-due-amount">KES <span id="pay-total">0.00</span></strong>
        </div>

        <div id="panel-cash" class="pay-panel" role="tabpanel" aria-labelledby="tab-cash">
            <div class="form-group">
                <label class="label" for="cash-tendered">Amount received (KES)</label>
                <input type="number" id="cash-tendered" class="field field-lg" min="0" step="0.01" inputmode="decimal" placeholder="0.00">
            </div>
            <div class="pay-change">
                <span>Change</span>
                <strong class="pay-change-amount">KES <span id="cash-change">0.00</span></strong>
            </div>
            <p id="cash-error" class="field-error" role="alert" hidden></p>
        </div>

        <div id="panel-mpesa" class="pay-panel" role="tabpanel" aria-labelledby="tab-mpesa" hidden>
            <div class="form-group">
                <label class="label" for="mpesa-phone">Customer phone number</label>
                <div class="mpesa-send-row">
                    <input type="tel" id="mpesa-phone" class="field" placeholder="0712345678" inputmode="numeric" maxlength="10" autocomplete="off">
                    <button type="button" id="mpesa-send" class="btn btn-mpesa">Send prompt</button>
                </div>
                <p class="muted mpesa-hint">The customer gets a prompt on this phone and enters their M-Pesa PIN.</p>
            </div>

            <div id="mpesa-status" class="mpesa-status" role="status" hidden>
                <span class="mpesa-spinner" aria-hidden="true"></span>
                <span id="mpesa-status-text"></span>
            </div>

            <p id="mpesa-error" class="field-error" role="alert" hidden></p>
        </div>

        <p id="pay-error" class="field-error pay-error" role="alert" hidden></p>
    </div>

    <div class="pay-dialog-actions">
        <button type="button" id="pay-cancel" class="btn">Cancel</button>
        <button type="button" id="complete-sale" class="btn btn-primary" disabled>Complete sale</button>
    </div>
</dialog>
