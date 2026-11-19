<?php
class UserController {
    public function index(): void { Auth::requireRole('Admin'); render('admin/users', ['users' => (new User())->all()]); }
    public function create(): void {
        Auth::requireRole('Admin');
        $u = trim($_POST['username'] ?? ''); $n = trim($_POST['full_name'] ?? ''); $pw = $_POST['password'] ?? ''; $role = $_POST['role'] ?? 'Cashier';
        if ($u === '' || $n === '' || strlen($pw) < 6 || !in_array($role, ['Admin', 'Cashier'], true)) { flash('error', 'Username, name, role and a 6+ character password are required.'); redirect('admin/users'); }
        try { (new User())->create($u, $n, $pw, $role); AuditLogger::log(Auth::user()['id'], 'USER_CREATE', "$u ($role)"); flash('success', 'User added.'); }
        catch (PDOException $e) { flash('error', 'That username is taken.'); }
        redirect('admin/users');
    }
    public function update(string $id): void {
        Auth::requireRole('Admin'); $role = $_POST['role'] ?? 'Cashier';
        if (in_array($role, ['Admin', 'Cashier'], true)) (new User())->update((int)$id, trim($_POST['full_name'] ?? ''), $role);
        AuditLogger::log(Auth::user()['id'], 'USER_UPDATE', "id=$id"); redirect('admin/users');
    }
    public function resetPassword(string $id): void {
        Auth::requireRole('Admin'); $tmp = 'Tmp' . random_int(100000, 999999);
        (new User())->setPassword((int)$id, $tmp); (new User())->resetFails((int)$id);
        AuditLogger::log(Auth::user()['id'], 'USER_PW_RESET', "id=$id"); flash('success', "Temporary password: $tmp (shown once)."); redirect('admin/users');
    }
    public function unlock(string $id): void { Auth::requireRole('Admin'); (new User())->resetFails((int)$id); AuditLogger::log(Auth::user()['id'], 'USER_UNLOCK', "id=$id"); redirect('admin/users'); }
    public function toggle(string $id): void {
        Auth::requireRole('Admin');
        if ((int)$id === Auth::user()['id']) { flash('error', 'You cannot deactivate your own account.'); redirect('admin/users'); }
        (new User())->toggle((int)$id); AuditLogger::log(Auth::user()['id'], 'USER_TOGGLE', "id=$id"); redirect('admin/users');
    }
}
