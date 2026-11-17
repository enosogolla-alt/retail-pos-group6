<?php ob_start(); ?>
<h1>Dashboard</h1>
<section class="cards">
    <div class="card"><h3>Today's Sales</h3><p><?= kes($summary['total'] ?? 0) ?></p></div>
    <div class="card"><h3>Transactions</h3><p><?= (int)($summary['txns'] ?? $summary['t'] ?? 0) ?></p></div>
    <div class="card"><h3>VAT</h3><p><?= kes($summary['vat'] ?? 0) ?></p></div>
    <div class="card alert"><h3>Low Stock</h3><p><?= count($lowStock) ?></p></div>
</section>
<h2>Low-stock items</h2>
<table><thead><tr><th>Product</th><th>Stock</th><th>Reorder</th></tr></thead><tbody>
<?php foreach ($lowStock as $p): ?>
    <tr><td><?= e($p['name']) ?></td><td><?= (int)$p['stock_qty'] ?></td><td><?= (int)$p['reorder_level'] ?></td></tr>
<?php endforeach; ?>
<?php if (!$lowStock): ?><tr><td colspan="3" class="empty">Everything is above its reorder level.</td></tr><?php endif; ?>
</tbody></table>
<h2>Recent sales</h2>
<table><thead><tr><th>Receipt</th><th>Cashier</th><th>Total</th></tr></thead><tbody>
<?php foreach ($recent as $s): ?>
    <tr><td><?= e($s['receipt_no']) ?></td><td><?= e($s['username']) ?></td><td><?= kes($s['total']) ?></td></tr>
<?php endforeach; ?>
<?php if (!$recent): ?><tr><td colspan="3" class="empty">No sales yet.</td></tr><?php endif; ?>
</tbody></table>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/admin_layout.php'; ?>
