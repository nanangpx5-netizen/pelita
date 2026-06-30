<?php
/**
 * Migration script for github_updates table
 * Run: php scripts/migrate_github_table.php
 */

require_once __DIR__ . '/../config/app.php';
require_once CLASSES_PATH . '/Database.php';

try {
    $db = Database::getInstance();
    $sql = file_get_contents(__DIR__ . '/../sql/2026-06-29_add_github_updates.sql');

    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            echo "Executing: " . substr($stmt, 0, 60) . "...\n";
            $db->getConnection()->exec($stmt);
        }
    }

    echo "Migration for github_updates table successful.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "Table already exists. Continuing...\n";
    }
}
