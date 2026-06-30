<?php
/**
 * PELITA - Manual Sync Trigger (POST only)
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/SyncManager.php';
require_once INCLUDES_PATH . '/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('admin/sync/'));
    exit;
}

session_start();

try {
    $manager = new SyncManager();
    $result = $manager->sync();
    $_SESSION['sync_result'] = $result;
} catch (Exception $e) {
    $_SESSION['sync_result'] = [
        'status' => false,
        'message' => $e->getMessage()
    ];
}

header('Location: ' . base_url('admin/sync/'));
exit;
