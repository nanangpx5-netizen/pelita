<?php
// Mock Server Environment for Config
$_SERVER['HTTP_HOST'] = 'localhost';

// Load Config & Classes
require_once __DIR__ . '/../config/app.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/BukuTamu.php';
require_once CLASSES_PATH . '/BukuTamuController.php';

echo "\n====== PELITA TEST SUITE ======\n\n";

$startTotal = microtime(true);
$passed = 0;
$failed = 0;

function assert_true($condition, $message) {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] $message\n";
        $passed++;
    } else {
        echo "[FAIL] $message\n";
        $failed++;
    }
}

// 1. Database Connection & Env Test
try {
    $db = Database::getInstance();
    assert_true($db->getConnection() instanceof PDO, "Database Connection Successful");
    assert_true(getenv('DB_HOST') !== false, "Environment Variables Loaded");
} catch (Exception $e) {
    assert_true(false, "Database Connection Failed: " . $e->getMessage());
    exit;
}

// 2. Unit Test: BukuTamuController Validation
echo "\n--- Controller Validation Tests ---\n";
$controller = new BukuTamuController();

// Case A: Empty Input
$result = $controller->store([]);
assert_true($result['success'] === false, "Validation: Reject Empty Input");
assert_true(isset($result['errors']['nama']), "Validation Error: Nama Required");

// Case B: Invalid Phone
$result = $controller->store([
    'nama' => 'Tester',
    'nohp' => 'abc', // Invalid
    'instansi' => 'Test',
    'keperluan' => 'Test'
]);
assert_true(isset($result['errors']['nohp']), "Validation Error: Invalid Phone");

// Case C: Invalid Email
$result = $controller->store([
    'nama' => 'Tester',
    'nohp' => '081234567890',
    'instansi' => 'Test',
    'keperluan' => 'Test',
    'email' => 'invalid-email'
]);
assert_true(isset($result['errors']['email']), "Validation Error: Invalid Email");

// Case D: Success
$testData = [
    'nama' => 'TEST_CONTROLLER_' . time(),
    'email' => 'test@example.com',
    'nohp' => '081234567890',
    'instansi' => 'Test Corp',
    'keperluan' => 'Konsultasi Statistik',
    'rincian' => 'Controller Test',
    'jam_datang' => date('H:i')
];

$result = $controller->store($testData);
assert_true($result['success'] === true, "Validation: Accept Valid Input");
assert_true(!empty($result['nomor_antrian']), "Controller: Generate Queue Number");

// Cleanup
if ($result['success'] && isset($result['data_id'])) {
    $db->delete('buku_tamu', "id = :id", ['id' => $result['data_id']]);
}

echo "\n====== SUMMARY ======\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Total Time: " . round((microtime(true) - $startTotal) * 1000, 2) . "ms\n";
