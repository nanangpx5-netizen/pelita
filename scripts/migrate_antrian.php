<?php
/**
 * Migration script for antrian tables
 * Run: php scripts/migrate_antrian.php
 */

require_once __DIR__ . '/../config/app.php';
require_once CLASSES_PATH . '/Database.php';

try {
    $db = Database::getInstance();
    $sql = file_get_contents(__DIR__ . '/../sql/2026-06-30_add_antrian_layanan.sql');

    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            echo "Executing: " . substr($stmt, 0, 60) . "...\n";
            $db->getConnection()->exec($stmt);
        }
    }

    echo "Migration for antrian tables successful.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "Table already exists. Continuing...\n";
    }
}
