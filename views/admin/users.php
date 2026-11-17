<?php ob_start(); ?>
<h1>Users</h1>
<section class="panel">
<h2>Add user</h2>
<form method="post" action="<?= e(base_url('admin/users')) ?>" class="inline-form">
    <?= Csrf::field() ?>
    <input name="username" placeholder="Username" required>
    <input name="full_name" placeholder="Full name" required>
    <input name="password" placeholder="Temp password (6+)" required>
    <select name="role"><option>Cashier</option><option>Admin</option></select>
    <button type="submit">Add</button>
</form>
</section>

<table><thead><tr><th>Username</th><th>Name</th><th>Role</th><th>Active</th><th>Failed</th><th>Locked until</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($users as $row): ?>
<tr>
    <td><?= e($row['username']) ?></td>
    <td><?= e($row['full_name']) ?></td>
    <td><?= e($row['role']) ?></td>
    <td><span class="badge <?= $row['is_active'] ? 'badge-ok' : 'badge-off' ?>"><?= $row['is_active'] ? 'Active' : 'Inactive' ?></span></td>
    <td><?= (int)$row['failed_attempts'] ?></td>
    <td><?= e($row['locked_until'] ?? '—') ?></td>
    <td>
        <form method="post" action="<?= e(base_url("admin/users/{$row['id']}/reset")) ?>" style="display:inline">
            <?= Csrf::field() ?><button type="submit" class="btn-mini">Reset password</button>
        </form>
        <form method="post" action="<?= e(base_url("admin/users/{$row['id']}/unlock")) ?>" style="display:inline">
            <?= Csrf::field() ?><button type="submit" class="btn-mini">Unlock</button>
        </form>
        <form method="post" action="<?= e(base_url("admin/users/{$row['id']}/toggle")) ?>" style="display:inline">
            <?= Csrf::field() ?><button type="submit" class="btn-mini"><?= $row['is_active'] ? 'Deactivate' : 'Activate' ?></button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/admin_layout.php'; ?>
