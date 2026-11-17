/*
 * Sample products for the checkout screen.
 * TEMPORARY: replaced by GET /products/lookup once the products table is seeded (task 4.1, server side).
 * Owner: Winston Enos (M4)
 * Task: 4.1
 */
'use strict';

window.POS_SEED_PRODUCTS = [
    { id: 1, barcode: '6160000000011', name: 'Fresh Milk 500ml', category: 'Dairy', price: 65, stock: 38, active: true },
    { id: 2, barcode: '6160000000028', name: 'White Bread 400g', category: 'Bakery', price: 65, stock: 24, active: true },
    { id: 3, barcode: '6160000000035', name: 'Sugar 1kg', category: 'Groceries', price: 180, stock: 6, active: true },
    { id: 4, barcode: '6160000000042', name: 'Maize Flour 2kg', category: 'Groceries', price: 210, stock: 29, active: true },
    { id: 5, barcode: '6160000000059', name: 'Cooking Oil 1L', category: 'Groceries', price: 350, stock: 4, active: true },
    { id: 6, barcode: '6160000000066', name: 'Rice 1kg', category: 'Groceries', price: 220, stock: 18, active: true },
    { id: 7, barcode: '6160000000073', name: 'Soda 500ml', category: 'Drinks', price: 80, stock: 45, active: true },
    { id: 8, barcode: '6160000000080', name: 'Drinking Water 1L', category: 'Drinks', price: 70, stock: 3, active: true },
    { id: 9, barcode: '6160000000097', name: 'Washing Powder 1kg', category: 'Household', price: 320, stock: 12, active: true },
    { id: 10, barcode: '6160000000103', name: 'Toothpaste 140g', category: 'Personal care', price: 190, stock: 0, active: true },
    { id: 11, barcode: '6160000000110', name: 'Biscuits 200g', category: 'Groceries', price: 120, stock: 15, active: false },
];
