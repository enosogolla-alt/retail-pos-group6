<?php /* User management · Owner: Xavier Biko (M1) · Task 1.6 · Prototype: Users */ ?>
<?php
/**
 * Variables supplied by UserController::index():
 * @var array $users         Every staff account.
 * @var int   $currentUserId The signed-in Admin, who cannot deactivate themselves or change their own role.
 */
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Users</h1>
        <p class="page-subtitle">Accounts are deactivated, never deleted, so past sales stay linked to them.</p>
    </div>
    <div class="page-actions">
        <button
            type="button"
            class="btn btn-primary"
            data-dialog-open="user-dialog"
            data-reset
            data-mode="add"
            data-dialog-heading="Add user"
            data-fill="<?= e(json_encode(['action' => url('/admin/users'), 'role' => 'Cashier'])) ?>"
        >Add user</button>
    </div>
</div>

<section class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Username</th>
                    <th scope="col">Role</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <?php
                    $isSelf = (int) $user['id'] === $currentUserId;
                    $isActive = (int) $user['is_active'] === 1;
                    $isLocked = User::isLocked($user);
                    ?>
                    <tr>
                        <td class="cell-strong"><?= e($user['full_name']) ?><?= $isSelf ? ' <span class="muted">(you)</span>' : '' ?></td>
                        <td class="cell-mono"><?= e($user['username']) ?></td>
                        <td><span class="badge <?= $user['role'] === 'Admin' ? 'badge-info' : '' ?>"><?= e($user['role']) ?></span></td>
                        <td>
                            <?php if (!$isActive): ?>
                                <span class="badge">Inactive</span>
                            <?php elseif ($isLocked): ?>
                                <span class="badge badge-danger">Locked</span>
                            <?php elseif ((int) $user['must_change_pw'] === 1): ?>
                                <span class="badge badge-warning">Temporary password</span>
                            <?php else: ?>
                                <span class="badge badge-success">Active</span>
                            <?php endif; ?>
                        </td>
                        <td class="cell-actions">
                            <button
                                type="button"
                                class="btn-link"
                                data-dialog-open="user-dialog"
                                data-mode="edit"
                                data-dialog-heading="Edit <?= e($user['full_name']) ?>"
                                data-fill="<?= e(json_encode([
                                    'action' => url('/admin/users/' . $user['id']),
                                    'full_name' => $user['full_name'],
                                    'role' => $user['role'],
                                ])) ?>"
                            >Edit</button>
                            <form method="post" action="<?= e(url('/admin/users/' . $user['id'] . '/reset')) ?>" data-confirm="<?= e('Reset the password for ' . $user['username'] . '?') ?>">
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn-link">Reset password</button>
                            </form>
                            <?php if ($isLocked): ?>
                                <form method="post" action="<?= e(url('/admin/users/' . $user['id'] . '/unlock')) ?>">
                                    <?= Csrf::field() ?>
                                    <button type="submit" class="btn-link">Unlock</button>
                                </form>
                            <?php endif; ?>
                            <?php if (!$isSelf): ?>
                                <form method="post" action="<?= e(url('/admin/users/' . $user['id'] . '/toggle')) ?>" data-confirm="<?= e(($isActive ? 'Deactivate ' : 'Activate ') . $user['full_name'] . '?') ?>">
                                    <?= Csrf::field() ?>
                                    <button type="submit" class="btn-link <?= $isActive ? 'link-danger' : '' ?>"><?= $isActive ? 'Deactivate' : 'Activate' ?></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<dialog id="user-dialog" class="modal" aria-labelledby="user-dialog-title">
    <form method="post" action="<?= e(url('/admin/users')) ?>">
        <?= Csrf::field() ?>
        <div class="modal-head">
            <h2 class="modal-title" id="user-dialog-title" data-dialog-heading>Add user</h2>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="label" for="user-full-name">Full name</label>
                <input class="field" id="user-full-name" name="full_name" maxlength="100" required>
            </div>
            <div class="form-group" data-show-when="add">
                <label class="label" for="user-username">Username</label>
                <input class="field" id="user-username" name="username" maxlength="20" pattern="[a-z0-9._]{3,20}" autocomplete="off" required>
            </div>
            <div class="form-group">
                <label class="label" for="user-role">Role</label>
                <select class="field" id="user-role" name="role">
                    <option value="Cashier">Cashier</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>
            <div class="form-group" data-show-when="add">
                <label class="label" for="user-password">Temporary password</label>
                <input class="field" id="user-password" name="password" type="password" minlength="6" autocomplete="new-password" required>
            </div>
            <p class="muted">Passwords are stored only as secure hashes.</p>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn" data-dialog-close>Cancel</button>
            <button type="submit" class="btn btn-primary">Save user</button>
        </div>
    </form>
</dialog>
