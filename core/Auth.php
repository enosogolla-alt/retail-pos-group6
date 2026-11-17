<?php
class Auth {
    public static function user(): ?array { return $_SESSION['user'] ?? null; }
    public static function check(): bool { return isset($_SESSION['user']); }
    public static function role(): ?string { return $_SESSION['user']['role'] ?? null; }
    public static function requireLogin(): void { if (!self::check()) redirect('login'); }
    public static function requireRole(string $r): void {
        self::requireLogin();
        if (self::role() !== $r) { http_response_code(403); exit('Forbidden'); }
    }
    /** Returns an error message, or null on success. */
    public static function attempt(string $username, string $pw): ?string {
        $users = new User(); $u = $users->findByUsername($username);
        $bad = 'Invalid username or password.';
        if (!$u || !$u['is_active']) return $bad;
        if ($u['locked_until'] && strtotime($u['locked_until']) > time()) return 'Account locked. Try again later or ask an admin to unlock it.';
        if (!password_verify($pw, $u['password_hash'])) {
            $users->recordFail((int)$u['id']);
            AuditLogger::log((int)$u['id'], 'LOGIN_FAILED', $username);
            return $bad;
        }
        $users->resetFails((int)$u['id']);
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$u['id'], 'username' => $u['username'], 'name' => $u['full_name'], 'role' => $u['role']];
        AuditLogger::log((int)$u['id'], 'LOGIN', $username);
        return null;
    }
}
