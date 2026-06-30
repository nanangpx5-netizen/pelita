<?php
/**
 * PELITA - Selesai Antrian Handler
 * POST-only action endpoint
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/AntrianController.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/csrf.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/antrian/');
}

if (!validate_csrf()) {
    $_SESSION['flash_error'] = 'Sesi tidak valid.';
    redirect('admin/antrian/');
}

$controller = new AntrianController();
$result = $controller->selesai((int)($_POST['id'] ?? 0));

if ($result['success']) {
    $_SESSION['flash_success'] = $result['message'];
} else {
    $_SESSION['flash_error'] = $result['message'];
}

$redirect = $_POST['redirect'] ?? 'antrian';
redirect("admin/{$redirect}/");
