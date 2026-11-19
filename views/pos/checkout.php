<?php /* Checkout screen: product search, cart, order summary and payment buttons · Owner: Stacy Kwamboka (M3) · Tasks 3.1-3.4 · Prototype: Checkout */ ?>
<div class="checkout">

    <section class="checkout-items" aria-label="Sale items">
        <div class="scan-box">
            <label for="product-search" class="visually-hidden">Scan barcode or search product</label>
            <svg class="scan-box-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <circle cx="11" cy="11" r="7"></circle>
                <path d="M20 20l-3.5-3.5"></path>
            </svg>
            <input
                type="text"
                id="product-search"
                class="field scan-box-input"
                placeholder="Scan barcode or type product name, then press Enter"
                autocomplete="off"
                autofocus
            >
            <p id="search-message" class="field-error" role="alert" hidden></p>
        </div>

        <div class="quick-add" aria-labelledby="quick-add-title">
            <span class="quick-add-title" id="quick-add-title">Quick add</span>
            <?php foreach (['Fresh Milk 500ml', 'White Bread 400g', 'Sugar 1kg', 'Maize Flour 2kg', 'Cooking Oil 1L', 'Rice 1kg'] as $quickItem): ?>
                <button type="button" class="chip" data-quick-add="<?= e($quickItem) ?>"><?= e($quickItem) ?></button>
            <?php endforeach; ?>
        </div>

        <div class="card cart">
            <div class="card-header">
                <h2 class="card-title">Cart <span class="cart-count">(<span id="cart-count">0</span> items)</span></h2>
                <button type="button" id="clear-cart" class="btn-link" disabled>Clear cart</button>
            </div>
            <div class="table-wrap" id="cart-table" hidden>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Item</th>
                            <th scope="col" class="text-center">Qty</th>
                            <th scope="col" class="num">Price</th>
                            <th scope="col" class="num">Total</th>
                            <th scope="col"><span class="visually-hidden">Remove</span></th>
                        </tr>
                    </thead>
                    <tbody id="cart-body"></tbody>
                </table>
            </div>
            <div id="cart-empty" class="cart-empty">
                <svg class="cart-empty-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="9" cy="20" r="1.4"></circle>
                    <circle cx="18" cy="20" r="1.4"></circle>
                    <path d="M2 3h3l2.6 12.2a1.5 1.5 0 0 0 1.5 1.2h8.8a1.5 1.5 0 0 0 1.5-1.1L21 8H6.2"></path>
                </svg>
                <p class="cart-empty-title">The cart is empty</p>
                <p class="muted">Scan an item, search above, or use Quick add.</p>
            </div>
        </div>
    </section>

    <aside class="card checkout-summary" aria-labelledby="summary-title">
        <h2 class="card-title" id="summary-title">Order summary</h2>

        <dl class="summary-lines" id="order-summary">
            <div class="summary-line">
                <dt>Items</dt>
                <dd class="num" id="summary-items">0</dd>
            </div>
            <div class="summary-line">
                <dt>Subtotal</dt>
                <dd class="num">KES <span id="summary-subtotal">0.00</span></dd>
            </div>
            <div class="summary-line">
                <dt>Discount</dt>
                <dd class="num">&minus; KES <span id="summary-discount">0.00</span></dd>
            </div>
            <div class="summary-line">
                <dt>VAT 16% (included)</dt>
                <dd class="num">KES <span id="summary-vat">0.00</span></dd>
            </div>
        </dl>

        <div class="summary-total">
            <span>TOTAL</span>
            <span class="summary-total-amount">KES <span id="summary-total">0.00</span></span>
        </div>

        <button type="button" id="apply-discount" class="btn btn-block summary-action" disabled>Apply discount</button>
        <button type="button" id="pay-cash" class="btn btn-primary btn-lg btn-block" disabled>Pay Cash</button>
        <button type="button" id="pay-mpesa" class="btn btn-mpesa btn-lg btn-block" disabled>Pay M-Pesa</button>
        <button type="button" id="cancel-sale" class="btn btn-block summary-action" disabled>Cancel sale</button>
    </aside>

</div>
<?php require __DIR__ . '/payment.php'; ?>
