<?php
/**
 * Script to initialize SQLite database
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';

if (DB_TYPE !== 'sqlite') {
    die("Error: DB_TYPE is not set to 'sqlite' in .env\n");
}

try {
    echo "Initializing SQLite Database...\n";
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $sqlFile = __DIR__ . '/../sql/sqlite_schema.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQLite schema file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $conn->exec($stmt);
        }
    }
    
    echo "SQLite database initialized successfully at: " . DB_SQLITE_PATH . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
