<?php ob_start(); ?>
<h1>Sales reports</h1>
<form method="get" action="<?= e(base_url('admin/reports')) ?>">
    <input type="date" name="date" value="<?= e($date) ?>"><button type="submit">Go</button>
    <a href="<?= e(base_url('admin/reports/csv?date=' . $date)) ?>">Export CSV</a>
</form>
<section class="cards">
    <div class="card"><h3>Total</h3><p><?= kes($summary['total'] ?? 0) ?></p></div>
    <div class="card"><h3>Transactions</h3><p><?= (int)($summary['t'] ?? 0) ?></p></div>
    <div class="card"><h3>VAT</h3><p><?= kes($summary['vat'] ?? 0) ?></p></div>
    <div class="card"><h3>Avg sale</h3><p><?= kes($summary['avg_sale'] ?? 0) ?></p></div>
</section>
<h2>Top products</h2>
<table><thead><tr><th>Product</th><th>Qty</th><th>Revenue</th></tr></thead><tbody>
<?php foreach ($topProducts as $p): ?><tr><td><?= e($p['name']) ?></td><td><?= (int)$p['qty'] ?></td><td><?= kes($p['revenue']) ?></td></tr><?php endforeach; ?></tbody></table>
<h2>By cashier</h2>
<table><thead><tr><th>Cashier</th><th>Txns</th><th>Total</th></tr></thead><tbody>
<?php foreach ($byCashier as $c): ?><tr><td><?= e($c['username']) ?></td><td><?= (int)$c['txns'] ?></td><td><?= kes($c['total']) ?></td></tr><?php endforeach; ?></tbody></table>
<h2>All sales</h2>
<table><thead><tr><th>Receipt</th><th>Cashier</th><th>Total</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($sales as $s): ?>
<tr><td><?= e($s['receipt_no']) ?></td><td><?= e($s['cashier']) ?></td><td><?= kes($s['total']) ?></td><td><?= e($s['status']) ?></td>
<td><a href="<?= e(base_url('receipts/' . $s['receipt_no'] . '/copy')) ?>">Reprint</a>
<?php if ($s['status'] === 'COMPLETED'): ?>
<form method="post" action="<?= e(base_url('sales/' . $s['id'] . '/void')) ?>" style="display:inline"><?= Csrf::field() ?>
<input name="reason" placeholder="Void reason" required><button type="submit">Void</button></form>
<?php endif; ?></td></tr>
<?php endforeach; ?></tbody></table>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/admin_layout.php'; ?>
