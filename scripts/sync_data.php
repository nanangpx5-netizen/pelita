<?php
/**
 * Script Sinkronisasi Data Pelita
 * Dijalankan via Cron Job / Task Scheduler
 */

// Load Environment
require_once __DIR__ . '/../config/app.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/SyncManager.php';

echo "====== PELITA DATA SYNC ======\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $manager = new SyncManager();

    // Mode: --test untuk health check saja
    if (in_array('--test', $argv ?? [])) {
        echo "--- Connection Test ---\n";
        $test = $manager->testConnection();
        echo "Status: " . ($test['status'] ? 'OK' : 'FAILED') . "\n";
        echo "Message: " . $test['message'] . "\n";
        if (!empty($test['tables'])) {
            foreach ($test['tables'] as $table => $info) {
                $status = $info['exists'] ? "OK ({$info['count']} records)" : "MISSING";
                echo "Table $table: $status\n";
            }
        }
        exit($test['status'] ? 0 : 1);
    }

    // Mode: sync
    $result = $manager->sync();

    if ($result['status']) {
        echo "\nSync Completed.\n";
        echo "Buku Tamu: " . $result['stats']['buku_tamu'] . " records\n";
        echo "Kepuasan : " . $result['stats']['kepuasan'] . " records\n";
        if (!empty($result['stats']['errors'])) {
            echo "Errors   : " . count($result['stats']['errors']) . "\n";
            foreach ($result['stats']['errors'] as $err) {
                echo "  - $err\n";
            }
        }
    } else {
        echo "Sync Failed: " . $result['message'] . "\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "Critical Error: " . $e->getMessage() . "\n";
    exit(1);
}
