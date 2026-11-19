<?php ob_start(); ?>
<h1>Audit log</h1>
<form method="get" action="<?= e(base_url('admin/audit')) ?>">
    <select name="action"><option value="">— All actions —</option>
    <?php foreach ($actions as $a): ?><option value="<?= e($a) ?>" <?= (($_GET['action'] ?? '') === $a) ? 'selected' : '' ?>><?= e($a) ?></option><?php endforeach; ?></select>
    <input name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search username or details"><button type="submit">Filter</button>
</form>
<table><thead><tr><th>When</th><th>User</th><th>Action</th><th>Details</th><th>IP</th></tr></thead><tbody>
<?php foreach ($logs as $l): ?>
<tr><td><?= e($l['created_at']) ?></td><td><?= e($l['username'] ?? '—') ?></td><td><?= e($l['action']) ?></td><td><?= e($l['details']) ?></td><td><?= e($l['ip_address']) ?></td></tr>
<?php endforeach; ?></tbody></table>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/admin_layout.php'; ?>
