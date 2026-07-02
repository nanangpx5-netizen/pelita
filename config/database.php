<?php
/**
 * PELITA - Database Configuration
 * Dual Environment: Localhost & Online Hosting
 */

require_once __DIR__ . '/../classes/EnvLoader.php';

$envPath = __DIR__ . '/../.env';

// 1. Load file .env jika filenya ada (terutama saat online di hosting)
if (file_exists($envPath)) {
    EnvLoader::load($envPath);
}

// 2. Deteksi apakah aplikasi berjalan di localhost
$is_localhost = in_array($_SERVER['HTTP_HOST'] ?? 'localhost', ['localhost', '127.0.0.1']);

if ($is_localhost) {
    // ===================================================
    // OPSI A: OTOMATIS RUNNING DI LOCALHOST (XAMPP/Laragon)
    // ===================================================
    define('DB_TYPE', 'mysql');
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_NAME', 'pelita'); // Nama database local Anda
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    // ===================================================
    // OPSI B: ONLINE DI HOSTING (Membaca dari file .env)
    // ===================================================
    define('DB_TYPE', getenv('DB_TYPE') ?: 'mysql');
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
    define('DB_NAME', getenv('DB_NAME'));
    define('DB_USER', getenv('DB_USER'));
    define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
}

// Setingan Tambahan
define('DB_SQLITE_PATH', getenv('DB_SQLITE_PATH') ?: 'database/pelita.sqlite');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');