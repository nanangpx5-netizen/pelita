<?php
/**
 * Authentication Handler
 * @package PELITA
 * @version 1.0.0
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Login user
 */
function login(string $username, string $password): array {
    $admin = new Admin();
    $user = $admin->findByUsername($username);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Username tidak ditemukan'];
    }
    
    if (!$admin->verifyPassword($password, $user['password'])) {
        return ['success' => false, 'message' => 'Password salah'];
    }
    
    // Set session
    $_SESSION['pelita_admin'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'nama' => $user['nama'],
        'email' => $user['email'],
        'logged_in_at' => time()
    ];
    
    // Update last login
    $admin->updateLastLogin($user['id']);
    
    // Regenerate session ID
    session_regenerate_id(true);
    
    return ['success' => true, 'message' => 'Login berhasil'];
}

/**
 * Check if logged in
 */
function is_logged_in(): bool {
    return isset($_SESSION['pelita_admin']['id']);
}

/**
 * Get current admin
 */
function current_admin(): ?array {
    return $_SESSION['pelita_admin'] ?? null;
}

/**
 * Get admin ID
 */
function admin_id(): ?int {
    return $_SESSION['pelita_admin']['id'] ?? null;
}

/**
 * Get admin name
 */
function admin_name(): string {
    return $_SESSION['pelita_admin']['nama'] ?? 'Admin';
}

/**
 * Require login (redirect if not)
 */
function require_login(): void {
    if (!is_logged_in()) {
        flash('error', 'Silakan login terlebih dahulu');
        redirect('admin/login.php');
    }
}

/**
 * Logout
 */
function logout(): void {
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}
