<?php
/**
 * PELITA - Application Configuration
 * @version 1.0.0
 */

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

// Detect Environment (support host header with port, e.g. 127.0.0.1:8091)
$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$hostOnly = explode(':', $httpHost)[0];
$is_localhost = in_array($hostOnly, ['localhost', '127.0.0.1', '::1'], true);

// Base URL
define('BASE_URL', $is_localhost 
    ? 'http://localhost/pelita' 
    : 'https://bpsjember.my.id/pelita'
);

// Application Info
define('APP_NAME', 'PELITA');
define('APP_FULL_NAME', 'Pelayanan & Lihat Tamu');
define('APP_VERSION', '2.1.0');
define('APP_TAGLINE', 'Menerangi Pelayanan, Memandu Pembangunan');
define('APP_YEAR', date('Y'));

// Institution Info
define('INSTITUTION_NAME', 'BPS Kabupaten Jember');
define('INSTITUTION_ADDRESS', 'Jl. Cendrawasih No. 20, Jember 68121, East Java, Indonesia');
define('INSTITUTION_PHONE', '(0331) 487642');
define('INSTITUTION_FAX', '(0331) 427533');
define('INSTITUTION_EMAIL', 'bps3509@bps.go.id');
define('INSTITUTION_WEBSITE', 'https://jemberkab.bps.go.id');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('CLASSES_PATH', ROOT_PATH . '/classes');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('ASSETS_PATH', PUBLIC_PATH . '/assets');

// Pagination
define('ITEMS_PER_PAGE', 20);

// Upload Settings
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);
