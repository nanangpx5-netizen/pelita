<?php
/**
 * PELITA - Panggil Antrian Handler
 * POST-only action endpoint
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Antrian.php';
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
$model = new Antrian();

if (!empty($_POST['id'])) {
    $antrian = $model->getById((int) $_POST['id']);
    if ($antrian && $antrian['status'] === 'menunggu') {
        $ok = $model->panggil($antrian['id']);
        if ($ok) {
            $_SESSION['flash_success'] = "Nomor {$antrian['nomor_antrian']} dipanggil.";
        } else {
            $_SESSION['flash_error'] = 'Gagal memanggil antrian.';
        }
    } else {
        $_SESSION['flash_error'] = 'Antrian tidak valid atau sudah dipanggil.';
    }
} elseif (!empty($_POST['kode_layanan'])) {
    $result = $controller->panggilBerikutnya($_POST['kode_layanan']);
    if ($result['success']) {
        $_SESSION['flash_success'] = $result['message'];
    } else {
        $_SESSION['flash_error'] = $result['message'];
    }
}

$redirect = $_POST['redirect'] ?? 'antrian';
redirect("admin/{$redirect}/");
