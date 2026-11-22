/*
 * Admin screen behaviour shared by every admin page: dialogs, form filling,
 * table filters, the stock adjustment preview and confirmations.
 * Owner: Xavier Biko (M1)
 * Tasks: 1.2-1.6
 */
'use strict';

(function () {
    /* ---------- Dialogs ---------- */

    // An opener may carry data-fill (JSON of form field values, plus "action" for the form URL)
    // and data-dialog-heading, so one dialog serves both Add and Edit.
    document.querySelectorAll('[data-dialog-open]').forEach(function (opener) {
        opener.addEventListener('click', function () {
            const dialog = document.getElementById(opener.dataset.dialogOpen);
            if (!dialog) {
                return;
            }

            const form = dialog.querySelector('form');
            if (form && opener.dataset.reset !== undefined) {
                form.reset();
            }

            if (form && opener.dataset.fill) {
                const values = JSON.parse(opener.dataset.fill);
                Object.keys(values).forEach(function (name) {
                    if (name === 'action') {
                        form.setAttribute('action', values[name]);
                        return;
                    }
                    const field = form.elements.namedItem(name);
                    if (field) {
                        field.value = values[name];
                    }
                });
            }

            const heading = dialog.querySelector('[data-dialog-heading]');
            if (heading && opener.dataset.dialogHeading) {
                heading.textContent = opener.dataset.dialogHeading;
            }

            dialog.querySelectorAll('[data-show-when]').forEach(function (element) {
                element.hidden = opener.dataset.mode !== element.dataset.showWhen;
                element.querySelectorAll('input').forEach(function (input) {
                    input.disabled = element.hidden;
                });
            });

            dialog.showModal();
            updateStockPreview(dialog);

            const first = dialog.querySelector('input:not([type=hidden]):not([disabled]), select, textarea');
            if (first) {
                first.focus();
            }
        });
    });

    document.querySelectorAll('[data-dialog-close]').forEach(function (button) {
        button.addEventListener('click', function () {
            button.closest('dialog').close();
        });
    });

    /* ---------- Table filters ---------- */

    function applyFilters(tableId) {
        const table = document.getElementById(tableId);
        if (!table) {
            return;
        }

        const text = document.querySelector('[data-filter-text="' + tableId + '"]');
        const term = text ? text.value.trim().toLowerCase() : '';
        const selects = document.querySelectorAll('[data-filter-table="' + tableId + '"]');
        let visible = 0;

        table.querySelectorAll('tbody tr[data-search]').forEach(function (row) {
            let show = term === '' || row.dataset.search.toLowerCase().includes(term);

            selects.forEach(function (select) {
                if (select.value !== '' && row.dataset[select.dataset.filterKey] !== select.value) {
                    show = false;
                }
            });

            row.hidden = !show;
            if (show) {
                visible++;
            }
        });

        const empty = document.querySelector('[data-empty-for="' + tableId + '"]');
        if (empty) {
            empty.hidden = visible > 0;
        }
    }

    document.querySelectorAll('[data-filter-text]').forEach(function (input) {
        input.addEventListener('input', function () {
            applyFilters(input.dataset.filterText);
        });
    });

    document.querySelectorAll('[data-filter-table]').forEach(function (select) {
        select.addEventListener('change', function () {
            applyFilters(select.dataset.filterTable);
        });
    });

    /* ---------- Stock adjustment preview ---------- */

    function updateStockPreview(container) {
        const form = container.querySelector('form[data-stock-preview]');
        if (!form) {
            return;
        }

        const current = parseInt(form.elements.namedItem('current').value, 10) || 0;
        const type = form.elements.namedItem('type').value;
        const quantity = parseInt(form.elements.namedItem('quantity').value, 10);
        const output = form.querySelector('[data-new-stock]');
        const label = form.querySelector('[data-quantity-label]');

        if (label) {
            label.textContent = type === 'CORRECTION' ? 'Counted stock (exact number)' : (type === 'RESTOCK' ? 'Quantity to add' : 'Quantity to remove');
        }

        // Only a restock comes from a supplier; a disabled select is left out of the submitted form.
        const supplierField = form.querySelector('[data-supplier-field]');
        if (supplierField) {
            const isRestock = type === 'RESTOCK';
            const select = supplierField.querySelector('select');
            supplierField.hidden = !isRestock;
            select.disabled = !isRestock;
            select.required = isRestock;
        }

        form.querySelector('[data-current-stock]').textContent = current;

        if (Number.isNaN(quantity)) {
            output.textContent = '–';
            return;
        }

        const next = type === 'RESTOCK' ? current + quantity : (type === 'DAMAGE' ? current - quantity : quantity);
        output.textContent = next < 0 ? next + ' (not allowed)' : String(next);
    }

    document.querySelectorAll('form[data-stock-preview]').forEach(function (form) {
        form.addEventListener('input', function () {
            updateStockPreview(form.parentElement);
        });
        form.addEventListener('change', function () {
            updateStockPreview(form.parentElement);
        });
    });

    /* ---------- Confirmations and printing ---------- */

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-print]').forEach(function (button) {
        button.addEventListener('click', function () {
            window.print();
        });
    });

    // Prevents a second submit while the first request is still being processed.
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (event.defaultPrevented) {
                return;
            }
            const button = form.querySelector('button[type=submit]');
            if (button) {
                setTimeout(function () {
                    button.disabled = true;
                }, 0);
            }
        });
    });
})();
