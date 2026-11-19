<?php ob_start(); ?>
<h1>Stock</h1>
<section class="panel">
<h2>Adjust stock</h2>
<form method="post" action="<?= e(base_url('stock/adjust')) ?>" class="inline-form">
    <?= Csrf::field() ?>
    <select name="product_id" required>
        <option value="">— Product —</option>
        <?php foreach ($products as $p): ?>
            <option value="<?= (int)$p['id'] ?>"><?= e($p['name']) ?> (<?= (int)$p['stock_qty'] ?>)</option>
        <?php endforeach; ?>
    </select>
    <select name="type" required>
        <option value="RESTOCK">Restock</option>
        <option value="DAMAGE">Damage</option>
        <option value="CORRECTION">Correction</option>
    </select>
    <input name="qty" type="number" required placeholder="Qty (+/-)">
    <input name="reason" required placeholder="Reason">
    <button type="submit">Adjust</button>
</form>
</section>

<h2>Stock levels</h2>
<table><thead><tr><th>Product</th><th>Stock</th><th>Reorder</th></tr></thead><tbody>
<?php foreach ($products as $p): ?>
<tr><td><?= e($p['name']) ?></td><td><?= (int)$p['stock_qty'] ?></td><td><?= (int)$p['reorder_level'] ?></td></tr>
<?php endforeach; ?>
</tbody></table>

<h2>Movements</h2>
<table><thead><tr><th>Date</th><th>Product</th><th>Type</th><th>Qty</th><th>Reason</th><th>By</th></tr></thead><tbody>
<?php foreach ($movements as $m): ?>
<tr>
    <td><?= e($m['created_at']) ?></td>
    <td><?= e($m['product_name']) ?></td>
    <td><?= e($m['type']) ?></td>
    <td><?= (int)$m['qty_change'] ?></td>
    <td><?= e($m['reason']) ?></td>
    <td><?= e($m['username'] ?? '—') ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/admin_layout.php'; ?>
