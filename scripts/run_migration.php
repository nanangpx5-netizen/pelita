<?php
require_once __DIR__ . '/../config/app.php';
require_once CLASSES_PATH . '/Database.php';

try {
    $db = Database::getInstance();
    $sql = file_get_contents(__DIR__ . '/../sql/2026-02-11_add_sync_column.sql');
    
    // Split by semicolon to handle multiple statements if PDO doesn't support it directly in exec
    // But usually it does. Let's try raw exec first, if fails, split.
    // Actually, PDO::exec might only run the first statement if multiple are provided depending on driver.
    // Safer to split.
    
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            echo "Executing: " . substr($stmt, 0, 50) . "...\n";
            $db->getConnection()->exec($stmt);
        }
    }
    
    echo "Migration successful.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    // Check if column exists error (1060)
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Columns already exist. Continuing...\n";
    }
}
