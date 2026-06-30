<?php
/**
 * PELITA - Database Restore Script
 * Restore SQLite dari file backup
 * Usage: 
 *   php scripts/restore_db.php --list         (list available backups)
 *   php scripts/restore_db.php --file=xxx     (restore specific file)
 *   php scripts/restore_db.php --latest       (restore latest backup)
 */

require_once __DIR__ . '/../config/app.php';

echo "====== PELITA DATABASE RESTORE ======\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

$backup_dir = ROOT_PATH . '/backups';
$args = $argv ?? [];

if (!is_dir($backup_dir)) {
    echo "ERROR: Backup directory not found. Run backup_db.php first.\n";
    exit(1);
}

// List backups
if (in_array('--list', $args)) {
    $backups = glob("$backup_dir/pelita_sqlite_*.sqlite");
    usort($backups, fn($a, $b) => filemtime($b) - filemtime($a));

    if (empty($backups)) {
        echo "No SQLite backups found.\n";
        exit(0);
    }

    echo "Available backups:\n";
    echo str_repeat('-', 70) . "\n";
    foreach ($backups as $i => $file) {
        $meta_file = "$file.meta.json";
        $meta = file_exists($meta_file) ? json_decode(file_get_contents($meta_file), true) : null;
        $size = number_format(filesize($file) / 1024, 1);
        $date = date('Y-m-d H:i:s', filemtime($file));
        $records = $meta ? "BT={$meta['records']['buku_tamu']}, KP={$meta['records']['kepuasan']}" : '?';

        printf("  [%d] %s (%s KB) - %s\n", $i + 1, basename($file), $size, $records);
    }
    echo str_repeat('-', 70) . "\n";
    echo "Total: " . count($backups) . " backup(s)\n";
    exit(0);
}

// Restore
$restore_file = null;

// --latest
if (in_array('--latest', $args)) {
    $backups = glob("$backup_dir/pelita_sqlite_*.sqlite");
    usort($backups, fn($a, $b) => filemtime($b) - filemtime($a));
    if (empty($backups)) {
        echo "ERROR: No backups found.\n";
        exit(1);
    }
    $restore_file = $backups[0];
}

// --file=xxx
foreach ($args as $arg) {
    if (str_starts_with($arg, '--file=')) {
        $fname = substr($arg, 7);
        $restore_file = "$backup_dir/$fname";
        if (!file_exists($restore_file)) {
            echo "ERROR: File not found: $restore_file\n";
            exit(1);
        }
    }
}

if (!$restore_file) {
    echo "Usage:\n";
    echo "  php scripts/restore_db.php --list\n";
    echo "  php scripts/restore_db.php --latest\n";
    echo "  php scripts/restore_db.php --file=backup_filename.sqlite\n";
    exit(0);
}

echo "Restore from: " . basename($restore_file) . "\n";
echo "Size: " . number_format(filesize($restore_file) / 1024, 1) . " KB\n";

$target = ROOT_PATH . '/' . (defined('DB_SQLITE_PATH') ? DB_SQLITE_PATH : 'database/pelita.sqlite');

// Auto backup current DB before restore
$pre_restore = "$backup_dir/pelita_pre_restore_" . date('Y-m-d_His') . ".sqlite";
if (file_exists($target)) {
    copy($target, $pre_restore);
    echo "Pre-restore backup: " . basename($pre_restore) . "\n";
}

// Restore
if (copy($restore_file, $target)) {
    echo "\nRestore SUCCESS.\n";
    echo "Database restored to: $target\n";
} else {
    echo "\nRestore FAILED.\n";
    exit(1);
}
