<?php ob_start(); ?>
<div class="checkout-grid">
    <section class="checkout-left">
        <input id="search" type="text" placeholder="Scan barcode or type product name…" autofocus>
        <table id="cart-table"><thead><tr><th>Item</th><th>Price</th><th>Qty</th><th>Line</th><th></th></tr></thead>
            <tbody id="cart-body"><tr><td colspan="5" class="empty">Cart is empty</td></tr></tbody></table>
    </section>
    <aside class="checkout-right">
        <h3>Summary</h3>
        <div class="summary-line"><span>Subtotal</span><span id="sum-subtotal">KES 0.00</span></div>
        <div class="summary-line"><span>Discount</span><span id="sum-discount">KES 0.00</span></div>
        <div class="summary-line"><span>VAT (16%, included)</span><span id="sum-vat">KES 0.00</span></div>
        <div class="summary-line total"><span>Total</span><span id="sum-total">KES 0.00</span></div>
        <div class="discount-box"><label>Discount % (max 10)
            <input id="discount-input" type="number" min="0" max="10" value="0"></label></div>
        <button id="pay-cash" class="btn-primary" type="button">Pay Cash</button>
        <button id="pay-mpesa" class="btn-primary" type="button">Pay M-Pesa</button>
    </aside>
</div>

<div id="payment-dialog" class="payment-dialog" style="display:none">
  <div class="payment-card">
    <h2>Payment</h2>
    <p>Total: <strong id="pay-total">KES 0.00</strong></p>
    <div class="tabs"><button type="button" class="tab active" data-tab="cash">Cash</button><button type="button" class="tab" data-tab="mpesa">M-Pesa</button></div>
    <div id="tab-cash" class="tab-pane">
        <label>Amount received <input id="cash-amount" type="number" step="0.01" min="0"></label>
        <p>Change: <strong id="cash-change">KES 0.00</strong></p>
    </div>
    <div id="tab-mpesa" class="tab-pane" style="display:none">
        <label>Customer phone <input id="mpesa-phone" type="text" placeholder="2547XXXXXXXX"></label>
        <button type="button" id="mpesa-send" class="btn-secondary">Send prompt</button>
        <p id="mpesa-status">Waiting…</p>
    </div>
    <div class="dialog-actions">
        <button id="complete-sale" class="btn-primary" type="button" disabled>Complete sale</button>
        <button id="cancel-payment" class="btn-secondary" type="button">Cancel</button>
    </div>
  </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/pos_layout.php'; ?>
