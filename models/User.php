<?php
class User {
    public function find(int $id): ?array { return q('SELECT * FROM users WHERE id=?', [$id])->fetch() ?: null; }
    public function findByUsername(string $u): ?array { return q('SELECT * FROM users WHERE username=?', [$u])->fetch() ?: null; }
    public function all(): array { return q('SELECT * FROM users ORDER BY username')->fetchAll(); }
    public function create(string $u, string $n, string $pw, string $role): int {
        q('INSERT INTO users(username,full_name,password_hash,role) VALUES (?,?,?,?)', [$u, $n, password_hash($pw, PASSWORD_DEFAULT), $role]);
        return (int)db()->lastInsertId();
    }
    public function update(int $id, string $n, string $role): void { q('UPDATE users SET full_name=?, role=? WHERE id=?', [$n, $role, $id]); }
    public function setPassword(int $id, string $pw): void { q('UPDATE users SET password_hash=? WHERE id=?', [password_hash($pw, PASSWORD_DEFAULT), $id]); }
    public function recordFail(int $id): void {
        q('UPDATE users SET failed_attempts=failed_attempts+1,
           locked_until=IF(failed_attempts>=?, DATE_ADD(NOW(), INTERVAL ? MINUTE), locked_until) WHERE id=?',
          [config('max_failed_logins', 5), config('lock_minutes', 15), $id]);
    }
    public function resetFails(int $id): void { q('UPDATE users SET failed_attempts=0, locked_until=NULL WHERE id=?', [$id]); }
    public function toggle(int $id): void { q('UPDATE users SET is_active = 1 - is_active WHERE id=?', [$id]); }
}
