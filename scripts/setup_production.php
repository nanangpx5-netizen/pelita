<?php
/**
 * Script Deployment Production PELITA
 * Membuat konfigurasi environment aman via .htaccess
 * 
 * Usage: php scripts/setup_production.php
 */

echo "====== SETUP PRODUCTION ENVIRONMENT PELITA ======\n\n";

// Function to prompt input
function prompt($message, $default = null) {
    echo $message . ($default ? " [$default]" : "") . ": ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    $input = trim($line);
    return $input ?: $default;
}

// 1. Collect Credentials
echo "Masukkan Konfigurasi Database Production:\n";
$host = prompt("DB Host", "localhost");
$port = prompt("DB Port", "3306");
$name = prompt("DB Name", "pelita_prod");
$user = prompt("DB User", "root");
$pass = prompt("DB Password", "");
$charset = prompt("DB Charset", "utf8mb4");

// 2. Prepare .htaccess content
$htaccessContent = <<<EOT

# --- PELITA ENVIRONMENT CONFIG START ---
<IfModule mod_env.c>
    SetEnv DB_HOST "$host"
    SetEnv DB_PORT "$port"
    SetEnv DB_NAME "$name"
    SetEnv DB_USER "$user"
    SetEnv DB_PASS "$pass"
    SetEnv DB_CHARSET "$charset"
</IfModule>
# --- PELITA ENVIRONMENT CONFIG END ---

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Block Access to .env and hidden files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

EOT;

// 3. Write to .htaccess in root
$rootPath = realpath(__DIR__ . '/..');
$htaccessPath = $rootPath . '/.htaccess';

echo "\nMenulis konfigurasi ke: $htaccessPath\n";

if (file_exists($htaccessPath)) {
    $currentContent = file_get_contents($htaccessPath);
    // Remove old config block if exists
    $pattern = '/# --- PELITA ENVIRONMENT CONFIG START ---.*# --- PELITA ENVIRONMENT CONFIG END ---/s';
    if (preg_match($pattern, $currentContent)) {
        echo "Mengupdate konfigurasi lama...\n";
        $newContent = preg_replace($pattern, trim($htaccessContent), $currentContent);
    } else {
        echo "Menambahkan konfigurasi baru...\n";
        $newContent = $currentContent . "\n" . $htaccessContent;
    }
} else {
    echo "Membuat file .htaccess baru...\n";
    $newContent = $htaccessContent;
}

if (file_put_contents($htaccessPath, $newContent)) {
    echo "\n[SUKSES] Konfigurasi berhasil disimpan!\n";
    echo "File .htaccess telah diperbarui dengan variabel environment.\n";
    echo "Anda TIDAK PERLU mengupload file .env ke server.\n";
} else {
    echo "\n[GAGAL] Tidak dapat menulis ke file .htaccess. Cek permission.\n";
}
