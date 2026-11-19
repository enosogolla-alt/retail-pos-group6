<?php
class AuthController {
    public function showLogin(): void { if (Auth::check()) redirect(''); render('auth/login'); }
    public function login(): void {
        $err = Auth::attempt(trim($_POST['username'] ?? ''), $_POST['password'] ?? '');
        if ($err) { flash('error', $err); redirect('login'); }
        redirect('');
    }
    public function logout(): void {
        if (Auth::check()) AuditLogger::log(Auth::user()['id'], 'LOGOUT', Auth::user()['username']);
        $_SESSION = []; session_destroy(); session_start(); redirect('login');
    }
}
