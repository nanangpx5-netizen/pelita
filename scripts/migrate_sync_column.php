<?php
/**
 * Migration: Add synced_at column (SQLite compatible)
 */
require_once __DIR__ . '/../config/app.php';
require_once CLASSES_PATH . '/Database.php';

$db = Database::getInstance();
$pdo = $db->getConnection();

$tables = ['buku_tamu', 'kepuasan'];

foreach ($tables as $table) {
    try {
        // Check if column already exists
        $cols = $pdo->query("PRAGMA table_info($table)")->fetchAll();
        $colNames = array_column($cols, 'name');

        if (in_array('synced_at', $colNames)) {
            echo "Column synced_at already exists in $table. Skipping.\n";
            continue;
        }

        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `synced_at` DATETIME DEFAULT NULL");
        echo "Added synced_at to $table\n";
    } catch (Exception $e) {
        echo "Error on $table: " . $e->getMessage() . "\n";
    }
}

echo "Migration done.\n";
