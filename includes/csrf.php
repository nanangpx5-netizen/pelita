<?php
/**
 * CSRF Protection
 * @package PELITA
 * @version 1.0.0
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate CSRF token
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generate CSRF hidden field
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Verify CSRF token
 */
function verify_csrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Validate CSRF from POST
 */
function validate_csrf(): bool {
    $token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf($token)) {
        if (is_ajax()) {
            json_response(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        }
        flash('error', 'Sesi telah berakhir. Silakan refresh halaman.');
        return false;
    }
    
    return true;
}

/**
 * Regenerate CSRF token
 */
function regenerate_csrf(): string {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
