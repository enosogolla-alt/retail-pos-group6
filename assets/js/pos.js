'use strict';

(function () {
    const checkout = document.querySelector('.checkout');
    const searchBox = document.getElementById('product-search');
    if (!checkout || !searchBox || !window.PosCart) {
        return;
    }

    const Pos = window.PosCart;
    const cart = Pos.createCart();
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    const el = function (id) {
        return document.getElementById(id);
    };

    const searchMessage = el('search-message');
    const cartBody = el('cart-body');
    const cartTable = el('cart-table');
    const cartEmpty = el('cart-empty');
    const cartCount = el('cart-count');
    const clearButton = el('clear-cart');
    const cancelSaleButton = el('cancel-sale');
    const discountButton = el('apply-discount');
    const discountPanel = el('discount-panel');
    const discountInput = el('discount-input');
    const discountError = el('discount-error');
    const payCashButton = el('pay-cash');
    const payMpesaButton = el('pay-mpesa');

    const dialog = el('payment-dialog');
    const payTotal = el('pay-total');
    const payError = el('pay-error');
    const tabCash = el('tab-cash');
    const tabMpesa = el('tab-mpesa');
    const panelCash = el('panel-cash');
    const panelMpesa = el('panel-mpesa');
    const cashInput = el('cash-tendered');
    const cashChange = el('cash-change');
    const cashError = el('cash-error');
    const mpesaPhone = el('mpesa-phone');
    const mpesaSend = el('mpesa-send');
    const mpesaStatus = el('mpesa-status');
    const mpesaStatusText = el('mpesa-status-text');
    // The confirmation code arrives with the payment, so the cashier never types it.
    let mpesaCode = '';
    const mpesaError = el('mpesa-error');
    const completeButton = el('complete-sale');

    let method = 'cash';
    let busy = false;
    let pollTimer = null;

    /* ---------- Server requests ---------- */

    function request(url, options) {
        const settings = Object.assign({ headers: {} }, options);
        settings.headers.Accept = 'application/json';
        settings.headers['X-CSRF-Token'] = csrfToken;
        if (settings.body) {
            settings.headers['Content-Type'] = 'application/json';
            settings.body = JSON.stringify(settings.body);
        }

        return fetch(url, settings).then(function (response) {
            return response.json().catch(function () {
                return {};
            }).then(function (data) {
                if (!response.ok) {
                    throw new Error(data.error || 'Something went wrong. Try again.');
                }
                return data;
            });
        });
    }

    /* ---------- Messages and focus ---------- */

    function showMessage(text, isSuccess) {
        searchMessage.textContent = text;
        searchMessage.className = isSuccess ? 'field-success' : 'field-error';
        searchMessage.hidden = !text;
    }

    function focusSearch() {
        searchBox.focus();
    }

    // A barcode scanner types into whatever has focus, so focus returns to the
    // search box after any click that is not on a field or inside a dialog.
    document.addEventListener('click', function (event) {
        if (event.target.closest('dialog') || event.target.closest('#discount-panel')) {
            return;
        }
        if (!['INPUT', 'SELECT', 'TEXTAREA', 'A'].includes(event.target.tagName)) {
            focusSearch();
        }
    });

    /* ---------- Product lookup and cart ---------- */

    function lookup(code) {
        showMessage('', false);

        return request(checkout.dataset.lookupUrl + '?code=' + encodeURIComponent(code))
            .then(function (data) {
                const result = Pos.addItem(cart, data.product);
                showMessage(result.ok ? '' : result.error, false);
                render();
                return result.ok;
            })
            .catch(function (error) {
                showMessage(error.message, false);
                return false;
            });
    }

    function iconButton(label, text, extraClass, onClick) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'icon-btn' + (extraClass ? ' ' + extraClass : '');
        button.setAttribute('aria-label', label);
        button.textContent = text;
        button.addEventListener('click', onClick);
        return button;
    }

    function cell(content, className) {
        const td = document.createElement('td');
        if (className) {
            td.className = className;
        }
        if (content instanceof Node) {
            td.appendChild(content);
        } else {
            td.textContent = content;
        }
        return td;
    }

    function changeQuantity(productId, quantity) {
        const result = Pos.setQuantity(cart, productId, quantity);
        showMessage(result.ok ? '' : result.error, false);
        render();
    }

    function renderRows() {
        cartBody.replaceChildren();

        cart.items.forEach(function (line) {
            const row = document.createElement('tr');

            const item = document.createElement('div');
            const name = document.createElement('div');
            name.className = 'cart-item-name';
            name.textContent = line.name;
            const code = document.createElement('div');
            code.className = 'cart-item-code';
            code.textContent = line.barcode;
            item.append(name, code);

            const qty = document.createElement('div');
            qty.className = 'qty-control';
            const value = document.createElement('span');
            value.className = 'qty-value';
            value.textContent = line.quantity;
            qty.append(
                iconButton('Decrease quantity of ' + line.name, '−', '', function () {
                    changeQuantity(line.productId, line.quantity - 1);
                }),
                value,
                iconButton('Increase quantity of ' + line.name, '+', '', function () {
                    changeQuantity(line.productId, line.quantity + 1);
                })
            );

            const remove = iconButton('Remove ' + line.name, '×', 'icon-btn-danger', function () {
                Pos.removeItem(cart, line.productId);
                render();
            });

            row.append(
                cell(item),
                cell(qty, 'text-center'),
                cell(Pos.money(line.unitPrice), 'num'),
                cell(Pos.money(Pos.lineTotal(line)), 'num'),
                cell(remove, 'num')
            );
            cartBody.appendChild(row);
        });
    }

    function render() {
        const totals = Pos.totals(cart);
        const hasItems = cart.items.length > 0;

        renderRows();
        cartTable.hidden = !hasItems;
        cartEmpty.hidden = hasItems;
        cartCount.textContent = totals.itemCount;

        el('summary-items').textContent = totals.itemCount;
        el('summary-subtotal').textContent = Pos.money(totals.subtotal);
        el('summary-discount').textContent = Pos.money(totals.discount);
        el('summary-vat').textContent = Pos.money(totals.vat);
        el('summary-total').textContent = Pos.money(totals.total);

        [clearButton, cancelSaleButton, discountButton, payCashButton, payMpesaButton].forEach(function (button) {
            button.disabled = !hasItems;
        });

        if (!hasItems) {
            discountPanel.hidden = true;
        }
    }

    searchBox.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter') {
            return;
        }
        event.preventDefault();

        const term = searchBox.value.trim();
        if (term === '') {
            return;
        }

        lookup(term).then(function (added) {
            if (added) {
                searchBox.value = '';
            }
        });
    });

    document.querySelectorAll('[data-quick-add]').forEach(function (chip) {
        chip.addEventListener('click', function () {
            lookup(chip.dataset.quickAdd);
        });
    });

    function resetSale() {
        Pos.clearCart(cart);
        discountInput.value = '';
        discountError.hidden = true;
        render();
    }

    clearButton.addEventListener('click', function () {
        resetSale();
        showMessage('', false);
    });

    cancelSaleButton.addEventListener('click', function () {
        resetSale();
        showMessage('Sale cancelled.', false);
    });

    /* ---------- Discount ---------- */

    discountButton.addEventListener('click', function () {
        discountPanel.hidden = false;
        discountInput.value = cart.discountPercent ? String(cart.discountPercent) : '';
        discountError.hidden = true;
        discountInput.focus();
    });

    function applyDiscount() {
        const result = Pos.applyDiscount(cart, discountInput.value);
        if (!result.ok) {
            discountError.textContent = result.error;
            discountError.hidden = false;
            return;
        }
        discountPanel.hidden = true;
        render();
        showMessage(cart.discountPercent + '% discount applied.', true);
    }

    el('discount-apply').addEventListener('click', applyDiscount);
    discountInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            applyDiscount();
        }
    });
    el('discount-cancel').addEventListener('click', function () {
        discountPanel.hidden = true;
    });

    /* ---------- Payment ---------- */

    function currentTotal() {
        return Pos.totals(cart).total;
    }

    function showPayError(text) {
        payError.textContent = text;
        payError.hidden = !text;
    }

    function stopPolling() {
        if (pollTimer !== null) {
            clearTimeout(pollTimer);
            pollTimer = null;
        }
    }

    function resetMpesa() {
        stopPolling();
        mpesaPhone.readOnly = false;
        mpesaSend.disabled = false;
        mpesaSend.textContent = 'Send prompt';
        mpesaStatus.hidden = true;
        mpesaStatus.className = 'mpesa-status';
        mpesaCode = '';
    }

    function setMpesaStatus(state, text) {
        mpesaStatus.hidden = false;
        mpesaStatus.className = 'mpesa-status' + (state ? ' is-' + state : '');
        mpesaStatusText.textContent = text;
    }

    function showTab(which) {
        method = which;
        const isCash = which === 'cash';

        panelCash.hidden = !isCash;
        panelMpesa.hidden = isCash;
        tabCash.classList.toggle('is-active', isCash);
        tabMpesa.classList.toggle('is-active', !isCash);
        tabCash.setAttribute('aria-selected', String(isCash));
        tabMpesa.setAttribute('aria-selected', String(!isCash));

        showPayError('');
        checkPayment();
        (isCash ? cashInput : mpesaPhone).focus();
    }

    function checkPayment() {
        let valid;

        if (method === 'cash') {
            const result = Pos.checkCash(currentTotal(), cashInput.value);
            cashChange.textContent = Pos.money(result.change);
            cashError.textContent = result.error || '';
            cashError.hidden = !result.error;
            valid = result.ok;
        } else {
            const result = Pos.checkMpesa(mpesaPhone.value, mpesaCode);
            mpesaError.textContent = result.error || '';
            mpesaError.hidden = !result.error;
            valid = result.ok;
        }

        completeButton.disabled = !valid || busy;
    }

    function pollStatus(requestId, startedAt) {
        request(checkout.dataset.stkStatusUrl + '?id=' + encodeURIComponent(requestId))
            .then(function (data) {
                if (data.status === 'PAID') {
                    mpesaCode = data.mpesa_code;
                    mpesaSend.textContent = 'Paid';
                    setMpesaStatus('paid', 'Payment received · code ' + data.mpesa_code);
                    checkPayment();
                    return;
                }

                if (data.status !== 'PENDING') {
                    mpesaPhone.readOnly = false;
                    mpesaSend.disabled = false;
                    mpesaSend.textContent = 'Send again';
                    setMpesaStatus('failed', data.message);
                    return;
                }

                // A real prompt expires after about a minute, so stop waiting after that.
                if (Date.now() - startedAt > 60000) {
                    mpesaPhone.readOnly = false;
                    mpesaSend.disabled = false;
                    mpesaSend.textContent = 'Send again';
                    setMpesaStatus('failed', 'No response from the customer. Check the number and send the prompt again.');
                    return;
                }

                pollTimer = setTimeout(function () {
                    pollStatus(requestId, startedAt);
                }, 2000);
            })
            .catch(function (error) {
                mpesaSend.disabled = false;
                setMpesaStatus('failed', error.message);
            });
    }

    mpesaSend.addEventListener('click', function () {
        const phone = mpesaPhone.value.trim();
        if (!/^0[17]\d{8}$/.test(phone)) {
            mpesaError.textContent = 'Enter a valid Safaricom number, for example 0712345678.';
            mpesaError.hidden = false;
            return;
        }

        stopPolling();
        mpesaError.hidden = true;
        mpesaPhone.readOnly = true;
        mpesaSend.disabled = true;
        mpesaSend.textContent = 'Waiting…';
        mpesaCode = '';
        checkPayment();
        setMpesaStatus('', 'Sending the prompt…');

        request(checkout.dataset.stkUrl, { method: 'POST', body: { phone: phone, amount: currentTotal() } })
            .then(function (data) {
                setMpesaStatus('', data.message + ' Waiting for the customer to enter their PIN…');
                pollStatus(data.checkout_request_id, Date.now());
            })
            .catch(function (error) {
                mpesaPhone.readOnly = false;
                mpesaSend.disabled = false;
                mpesaSend.textContent = 'Send prompt';
                setMpesaStatus('failed', error.message);
            });
    });

    function openPayment(which) {
        if (cart.items.length === 0) {
            return;
        }
        payTotal.textContent = Pos.money(currentTotal());
        cashInput.value = '';
        mpesaPhone.value = '';
        resetMpesa();
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

    el('pay-cancel').addEventListener('click', function () {
        dialog.close();
    });

    completeButton.addEventListener('click', function () {
        if (completeButton.disabled || busy) {
            return;
        }

        const payment = method === 'cash'
            ? { method: 'CASH', tendered: cashInput.value }
            : { method: 'MPESA', phone: mpesaPhone.value.trim(), mpesa_code: mpesaCode };

        busy = true;
        completeButton.disabled = true;
        completeButton.textContent = 'Saving…';
        showPayError('');

        request(checkout.dataset.saleUrl, {
            method: 'POST',
            body: {
                items: cart.items.map(function (line) {
                    return { product_id: line.productId, qty: line.quantity };
                }),
                discount_percent: cart.discountPercent,
                payment: payment,
            },
        })
            .then(function (data) {
                window.location.href = data.receipt_url + '?new=1';
            })
            .catch(function (error) {
                busy = false;
                completeButton.textContent = 'Complete sale';
                showPayError(error.message);
                checkPayment();
            });
    });

    // Fires for Cancel, the Esc key and after a sale.
    dialog.addEventListener('close', function () {
        stopPolling();
        focusSearch();
    });

    render();
    focusSearch();
})();
