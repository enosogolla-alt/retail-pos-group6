/*
 * Checkout logic: product lookup, cart totals, discount rules and payment checks.
 * Results are for display only; the server recalculates every amount when the sale is saved (task 4.5).
 * Owner: Winston Enos (M4)
 * Tasks: 4.1, 4.2, 4.3, 4.4
 */
'use strict';

window.PosCart = (function () {
    const VAT_RATE = 16;
    const MAX_DISCOUNT = 10;
    const MPESA_CODE_LENGTH = 10;

    // Amounts are held in cents so repeated additions never drift by a fraction of a shilling.
    function toCents(amount) {
        return Math.round(Number(amount) * 100);
    }

    function fromCents(cents) {
        return cents / 100;
    }

    /* ---------- 4.1 Product lookup ---------- */

    function activeProducts(products) {
        return products.filter(function (product) {
            return product.active;
        });
    }

    /**
     * An exact barcode wins over a name match, because a scanner always sends the full barcode.
     */
    function findProduct(products, code) {
        const term = String(code).trim().toLowerCase();
        if (term === '') {
            return null;
        }

        const active = activeProducts(products);

        return active.find(function (product) {
            return product.barcode === term;
        }) || active.find(function (product) {
            return product.name.toLowerCase().includes(term);
        }) || null;
    }

    function searchProducts(products, text, limit) {
        const term = String(text).trim().toLowerCase();
        if (term === '') {
            return [];
        }

        return activeProducts(products).filter(function (product) {
            return product.name.toLowerCase().includes(term) || product.barcode.startsWith(term);
        }).slice(0, limit || 6);
    }

    /* ---------- Cart ---------- */

    function createCart() {
        return { items: [], discountPercent: 0 };
    }

    function findLine(cart, productId) {
        return cart.items.find(function (line) {
            return line.productId === productId;
        });
    }

    function stockError(product) {
        return product.stock === 0
            ? product.name + ' is out of stock.'
            : 'Only ' + product.stock + ' ' + product.name + ' in stock.';
    }

    function addItem(cart, product) {
        const line = findLine(cart, product.id);
        const quantity = (line ? line.quantity : 0) + 1;

        if (quantity > product.stock) {
            return { ok: false, error: stockError(product) };
        }

        if (line) {
            line.quantity = quantity;
        } else {
            cart.items.push({
                productId: product.id,
                barcode: product.barcode,
                name: product.name,
                unitPrice: product.price,
                stock: product.stock,
                quantity: 1,
            });
        }

        return { ok: true, error: null };
    }

    function setQuantity(cart, productId, quantity) {
        const line = findLine(cart, productId);
        if (!line) {
            return { ok: false, error: 'This item is not in the cart.' };
        }

        if (quantity <= 0) {
            removeItem(cart, productId);
            return { ok: true, error: null };
        }

        if (quantity > line.stock) {
            return { ok: false, error: stockError({ name: line.name, stock: line.stock }) };
        }

        line.quantity = quantity;
        return { ok: true, error: null };
    }

    function removeItem(cart, productId) {
        cart.items = cart.items.filter(function (line) {
            return line.productId !== productId;
        });
    }

    function clearCart(cart) {
        cart.items = [];
        cart.discountPercent = 0;
    }

    /* ---------- 4.2 Totals ---------- */

    /**
     * Prices already include VAT, so VAT is extracted from the total rather than added to it.
     */
    function totals(cart) {
        const itemCount = cart.items.reduce(function (sum, line) {
            return sum + line.quantity;
        }, 0);

        const subtotal = cart.items.reduce(function (sum, line) {
            return sum + toCents(line.unitPrice) * line.quantity;
        }, 0);

        const discount = Math.round(subtotal * cart.discountPercent / 100);
        const total = subtotal - discount;
        const vat = Math.round(total * VAT_RATE / (100 + VAT_RATE));

        return {
            itemCount: itemCount,
            subtotal: fromCents(subtotal),
            discount: fromCents(discount),
            vat: fromCents(vat),
            total: fromCents(total),
        };
    }

    function lineTotal(line) {
        return fromCents(toCents(line.unitPrice) * line.quantity);
    }

    /* ---------- 4.3 Discount rules ---------- */

    function applyDiscount(cart, percent) {
        const value = Number(percent);

        if (String(percent).trim() === '' || Number.isNaN(value) || value < 0 || value > 100) {
            return { ok: false, error: 'Enter a discount between 0 and 100%.' };
        }

        if (value > MAX_DISCOUNT) {
            return { ok: false, error: 'The maximum discount is ' + MAX_DISCOUNT + '%.' };
        }

        cart.discountPercent = value;
        return { ok: true, error: null };
    }

    /* ---------- 4.4 Payment checks ---------- */

    function checkCash(total, tendered) {
        const totalCents = toCents(total);
        const tenderedValue = Number(tendered);

        if (String(tendered).trim() === '' || Number.isNaN(tenderedValue)) {
            return { ok: false, change: 0, error: null };
        }

        const tenderedCents = toCents(tenderedValue);
        if (tenderedCents < totalCents) {
            return { ok: false, change: 0, error: 'Amount received is less than the total.' };
        }

        return { ok: true, change: fromCents(tenderedCents - totalCents), error: null };
    }

    // Format checks only; whether a code was already used is checked by the server (task 4.4).
    function checkMpesa(phone, code) {
        const phoneOk = /^0[17]\d{8}$/.test(String(phone).trim());
        const codeValue = String(code).trim();
        const codeOk = new RegExp('^[A-Za-z0-9]{' + MPESA_CODE_LENGTH + '}$').test(codeValue);

        let error = null;
        if (codeValue !== '' && !codeOk) {
            error = 'The M-Pesa code must be exactly ' + MPESA_CODE_LENGTH + ' letters or numbers.';
        } else if (String(phone).trim() !== '' && !phoneOk) {
            error = 'Enter a valid Safaricom number, for example 0712345678.';
        }

        return { ok: phoneOk && codeOk, error: error };
    }

    function money(amount) {
        return Number(amount).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    return {
        VAT_RATE: VAT_RATE,
        MAX_DISCOUNT: MAX_DISCOUNT,
        findProduct: findProduct,
        searchProducts: searchProducts,
        createCart: createCart,
        addItem: addItem,
        setQuantity: setQuantity,
        removeItem: removeItem,
        clearCart: clearCart,
        totals: totals,
        lineTotal: lineTotal,
        applyDiscount: applyDiscount,
        checkCash: checkCash,
        checkMpesa: checkMpesa,
        money: money,
    };
})();
