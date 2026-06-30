<?php
/**
 * PELITA - Database Configuration
 * @version 1.1.0
 */

require_once __DIR__ . '/../classes/EnvLoader.php';

// Load .env file
$envPath = __DIR__ . '/../.env';
EnvLoader::load($envPath);

$is_localhost = in_array($_SERVER['HTTP_HOST'] ?? 'localhost', ['localhost', '127.0.0.1']);

// Database Type (mysql or sqlite)
define('DB_TYPE', getenv('DB_TYPE') ?: 'mysql');

// Database Credentials from ENV
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'pelita');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// SQLite specific (relative to root directory)
define('DB_SQLITE_PATH', getenv('DB_SQLITE_PATH') ?: 'database/pelita.sqlite');

define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');
