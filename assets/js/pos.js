(function () {
    'use strict';
    
    var searchBox = document.getElementById('product-search');
    if (!searchBox) { return; } // not on the checkout page

    // Put the cursor back in the search box.
    function focusSearch() {
        searchBox.focus();
    }

    // When the cashier clicks elsewhere, return the cursor to the search box,
    // unless they clicked a field they need to type in (like the payment dialog).
    document.addEventListener('click', function (event) {
        var tag = event.target.tagName;
        if (tag !== 'INPUT' && tag !== 'SELECT' && tag !== 'TEXTAREA') {
            focusSearch();
        }
    });

    focusSearch();
})();