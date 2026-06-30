<?php
/**
 * PELITA - Reset Admin Password Script
 * @version 1.0.0
 * 
 * Usage: 
 * php scripts/reset-admin-password.php <username> <new_password>
 * 
 * Example:
 * php scripts/reset-admin-password.php admin admin123
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Admin.php';

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    die('Script ini harus dijalankan dari command line/terminal');
}

// Check arguments
if ($argc < 3) {
    echo "Usage: php reset-admin-password.php <username> <new_password>\n";
    echo "Example: php reset-admin-password.php admin admin123\n";
    exit(1);
}

$username = $argv[1];
$newPassword = $argv[2];

// Validate input
if (empty($username) || empty($newPassword)) {
    echo "Error: Username dan password tidak boleh kosong\n";
    exit(1);
}

if (strlen($newPassword) < 5) {
    echo "Error: Password minimal harus 5 karakter\n";
    exit(1);
}

try {
    // Initialize database
    $admin = new Admin();
    
    // Find admin by username
    $user = $admin->findByUsername($username);
    
    if (!$user) {
        echo "Error: Admin dengan username '{$username}' tidak ditemukan\n";
        exit(1);
    }
    
    // Change password
    $success = $admin->changePassword($user['id'], $newPassword);
    
    if ($success) {
        echo "✓ Password admin '{$username}' berhasil direset!\n";
        echo "Username: {$username}\n";
        echo "Password baru: {$newPassword}\n";
        echo "\nSilakan login dengan kredensial baru di: admin/login.php\n";
        exit(0);
    } else {
        echo "Error: Gagal mengubah password\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
