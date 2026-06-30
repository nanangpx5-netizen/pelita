<?php
/**
 * PELITA - Database Backup Script
 * Backup SQLite lokal ke folder backups/
 * Usage: php scripts/backup_db.php
 */

require_once __DIR__ . '/../config/app.php';
require_once CLASSES_PATH . '/Database.php';

echo "====== PELITA DATABASE BACKUP ======\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// Setup backup directory
$backup_dir = ROOT_PATH . '/backups';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
    echo "Created backup directory: $backup_dir\n";
}

$db = Database::getInstance();

if ($db->isSqlite()) {
    // --- SQLite Backup ---
    $source = ROOT_PATH . '/' . DB_SQLITE_PATH;

    if (!file_exists($source)) {
        echo "ERROR: SQLite database not found at: $source\n";
        exit(1);
    }

    $timestamp = date('Y-m-d_His');
    $backup_file = "$backup_dir/pelita_sqlite_{$timestamp}.sqlite";

    // Get record counts before backup
    $bt_count = (int) $db->getConnection()->query("SELECT COUNT(*) FROM buku_tamu")->fetchColumn();
    $kp_count = (int) $db->getConnection()->query("SELECT COUNT(*) FROM kepuasan")->fetchColumn();

    // Copy file
    if (copy($source, $backup_file)) {
        $size = filesize($backup_file);
        echo "Backup created: $backup_file\n";
        echo "Size: " . number_format($size / 1024, 1) . " KB\n";
        echo "Records: Buku Tamu=$bt_count, Kepuasan=$kp_count\n";

        // Write metadata
        $meta = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'sqlite',
            'file' => basename($backup_file),
            'size_bytes' => $size,
            'records' => ['buku_tamu' => $bt_count, 'kepuasan' => $kp_count]
        ];
        file_put_contents("$backup_file.meta.json", json_encode($meta, JSON_PRETTY_PRINT));

        echo "\nBackup SUCCESS.\n";
    } else {
        echo "ERROR: Failed to copy database file.\n";
        exit(1);
    }

} elseif ($db->isMysql()) {
    // --- MySQL Backup via SQL dump ---
    $timestamp = date('Y-m-d_His');
    $backup_file = "$backup_dir/pelita_mysql_{$timestamp}.sql";

    $tables = ['admin', 'buku_tamu', 'kepuasan', 'ref_bulan', 'ref_pendidikan', 'ref_pekerjaan', 'ref_keperluan'];
    $output = "-- PELITA MySQL Backup\n-- Date: " . date('Y-m-d H:i:s') . "\n\n";

    foreach ($tables as $table) {
        try {
            // Get CREATE TABLE
            $createStmt = $db->getConnection()->query("SHOW CREATE TABLE `$table`")->fetch();
            $output .= "DROP TABLE IF EXISTS `$table`;\n";
            $output .= $createStmt['Create Table'] . ";\n\n";

            // Get data
            $rows = $db->getConnection()->query("SELECT * FROM `$table`")->fetchAll();
            foreach ($rows as $row) {
                $values = array_map(function ($v) use ($db) {
                    return $v === null ? 'NULL' : $db->getConnection()->quote($v);
                }, array_values($row));
                $output .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
            }
            $output .= "\n";
            echo "Table $table: " . count($rows) . " records\n";
        } catch (Exception $e) {
            echo "Skip table $table: " . $e->getMessage() . "\n";
        }
    }

    file_put_contents($backup_file, $output);
    echo "\nBackup created: $backup_file\n";
    echo "Size: " . number_format(filesize($backup_file) / 1024, 1) . " KB\n";
    echo "Backup SUCCESS.\n";
}

// Cleanup old backups (keep last 30)
$backups = glob("$backup_dir/pelita_*");
usort($backups, fn($a, $b) => filemtime($b) - filemtime($a));
$max_backups = 30;
if (count($backups) > $max_backups) {
    $to_delete = array_slice($backups, $max_backups);
    foreach ($to_delete as $old) {
        unlink($old);
        echo "Cleaned up old backup: " . basename($old) . "\n";
    }
}
