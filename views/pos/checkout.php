<?php // Checkout screen: search box, cart, summary and pay buttons (HTML only). ?>
<section class="checkout">

    <!-- Left: search + cart -->
    <div class="checkout__items">
        <div class="scan-box">
            <label for="product-search" class="scan-box__label">Scan barcode or search product</label>
            <input type="text"
                   id="product-search"
                   class="scan-box__input"
                   placeholder="Scan or type a name, then press Enter"
                   autocomplete="off"
                   autofocus>
            <p id="search-message" class="form-error" role="alert" hidden></p>
        </div>

        <div class="cart">
            <table class="cart__table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th><span class="visually-hidden">Remove</span></th>
                    </tr>
                </thead>
                <tbody id="cart-body">
                    <!-- rows are added by pos.js (task 3.2) -->
                </tbody>
            </table>
            <p id="cart-empty" class="cart__empty">No items yet. Scan a barcode to start a sale.</p>
        </div>
    </div>

    <!-- Right: order summary + pay buttons -->
    <aside class="checkout__side">
        <div class="summary" id="order-summary">
            <!-- totals are filled by pos.js (task 3.3) -->
        </div>

        <div class="pay-buttons">
            <button type="button" id="pay-cash" class="btn btn--primary" disabled>Pay cash </button>
            <button type="button" id="pay-mpesa" class="btn btn--primary" disabled>Pay M-Pesa </button>
        </div>
    </aside>

</section>