<?php ob_start(); ?>
<h1>Products</h1>
<section class="panel">
<h2>Add product</h2>
<form method="post" action="<?= e(base_url('admin/products')) ?>" class="inline-form">
    <?= Csrf::field() ?>
    <input name="barcode" placeholder="Barcode" required>
    <input name="name" placeholder="Name" required>
    <select name="category_id">
        <option value="">— No category —</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <input name="selling_price" type="number" step="0.01" min="0.01" placeholder="Price" required>
    <input name="reorder_level" type="number" min="0" value="0" placeholder="Reorder" required>
    <button type="submit">Add</button>
</form>
</section>

<table><thead><tr><th>Barcode</th><th>Name</th><th>Category</th><th>Price</th><th>Reorder</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($products as $p): ?>
<tr>
    <td><?= e($p['barcode']) ?></td>
    <td><?= e($p['name']) ?></td>
    <td><?= e($p['category_name'] ?? '—') ?></td>
    <td><?= kes($p['selling_price']) ?></td>
    <td><?= (int)$p['reorder_level'] ?></td>
    <td><?= (int)$p['stock_qty'] ?></td>
    <td><span class="badge <?= $p['is_active'] ? 'badge-ok' : 'badge-off' ?>"><?= $p['is_active'] ? 'Active' : 'Inactive' ?></span></td>
    <td>
        <form method="post" action="<?= e(base_url("admin/products/{$p['id']}/toggle")) ?>" style="display:inline">
            <?= Csrf::field() ?><button type="submit" class="btn-mini"><?= $p['is_active'] ? 'Deactivate' : 'Activate' ?></button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/admin_layout.php'; ?>
