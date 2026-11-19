<!doctype html>
<html><head><meta charset="utf-8"><title>Receipt <?= e($sale['receipt_no']) ?></title>
<link rel="stylesheet" href="<?= e(base_url('assets/css/app.css')) ?>">
<style>@media print { body { width: 80mm; font-size: 11px; font-family: monospace; } .no-print { display: none; } }</style>
</head><body class="receipt-page">
<?php if (!empty($isCopy)): ?><div class="copy-badge">COPY</div><?php endif; ?>
<h2><?= e($settings['shop_name'] ?? 'Retail POS') ?></h2>
<p>KRA PIN: <?= e($settings['kra_pin'] ?? '') ?></p><hr>
<p>Receipt: <?= e($sale['receipt_no']) ?><br>Date: <?= e($sale['created_at']) ?><?php if ($sale['status'] === 'VOID'): ?><br><strong>VOID</strong><?php endif; ?></p><hr>
<table><thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>
<?php foreach ($items as $it): ?>
<tr><td><?= e($it['name']) ?></td><td><?= (int)$it['qty'] ?></td><td><?= kes($it['unit_price']) ?></td><td><?= kes($it['line_total']) ?></td></tr>
<?php endforeach; ?></tbody></table><hr>
<p>Subtotal: <?= kes($sale['subtotal']) ?><br>Discount: <?= kes($sale['discount']) ?><br>VAT (incl.): <?= kes($sale['vat']) ?><br><strong>Total: <?= kes($sale['total']) ?></strong></p><hr>
<p>Payment:</p><ul>
<?php foreach ($payments as $p): ?>
<li><?= e($p['method']) ?> — <?= kes($p['amount']) ?><?php if (!empty($p['mpesa_code'])): ?> (<?= e($p['mpesa_code']) ?>)<?php endif; ?></li>
<?php endforeach; ?></ul>
<p class="no-print"><button onclick="window.print()">Print</button> <a href="<?= e(base_url('pos')) ?>">Back to POS</a></p>
</body></html>
