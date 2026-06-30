<?php
declare(strict_types=1);

/**
 * PELITA integration-style unit tests (plain PHP runner).
 * Run: php tests/run.php
 */

$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Admin.php';
require_once CLASSES_PATH . '/BukuTamu.php';
require_once CLASSES_PATH . '/Kepuasan.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/csrf.php';

$db = Database::getInstance();

$results = [
    'passed' => 0,
    'failed' => 0,
];

function test_assert(bool $condition, string $message): void {
    global $results;
    if ($condition) {
        $results['passed']++;
        echo "[PASS] {$message}\n";
        return;
    }

    $results['failed']++;
    echo "[FAIL] {$message}\n";
}

function reset_test_data(Database $db): void {
    $db->query('DELETE FROM buku_tamu');
    $db->query('DELETE FROM kepuasan');
}

reset_test_data($db);

echo "== Test 1: CSRF ==\n";
$token = csrf_token();
test_assert(strlen($token) === 64, 'CSRF token length is 64 chars');
test_assert(verify_csrf($token) === true, 'CSRF token verifies correctly');
test_assert(verify_csrf('invalid-token') === false, 'Invalid CSRF token is rejected');

echo "\n== Test 2: Database singleton + query ==\n";
$db2 = Database::getInstance();
test_assert($db === $db2, 'Database::getInstance returns singleton object');
$row = $db->fetch('SELECT COUNT(*) AS total FROM admin');
test_assert(isset($row['total']), 'Database fetch() returns associative row');
test_assert((int)$row['total'] >= 1, 'Admin table has at least one record');

echo "\n== Test 3: Admin model ==\n";
$admin = new Admin();
$adminUser = $admin->findByUsername('admin_pelita');
test_assert($adminUser !== null, 'findByUsername returns seeded admin');
if ($adminUser !== null) {
    test_assert($admin->verifyPassword('Admin123!', $adminUser['password']) === true, 'verifyPassword accepts known password');
    $admin->updateLastLogin((int)$adminUser['id']);
    $refreshed = $admin->findById((int)$adminUser['id']);
    test_assert(!empty($refreshed['last_login']), 'updateLastLogin updates timestamp');
}

echo "\n== Test 4: BukuTamu model ==\n";
$bukuTamu = new BukuTamu();
$id1 = $bukuTamu->create([
    'nama' => 'Tester 1',
    'email' => 'tester1@example.com',
    'alamat' => '-',
    'nohp' => '081111111111',
    'umur' => 30,
    'asal' => 'QA',
    'jenis_kelamin' => 'Laki-laki',
    'pendidikan' => 'S1',
    'pekerjaan' => 'Tester',
    'keperluan' => 'Konsultasi Statistik',
    'keperluan_lain' => 'Automated test',
    'tanggal' => date('Y-m-d'),
    'waktu' => '09:00:00',
]);
$id2 = $bukuTamu->create([
    'nama' => 'Tester 2',
    'email' => 'tester2@example.com',
    'alamat' => '-',
    'nohp' => '082222222222',
    'umur' => 28,
    'asal' => 'QA',
    'jenis_kelamin' => 'Perempuan',
    'pendidikan' => 'S1',
    'pekerjaan' => 'Tester',
    'keperluan' => 'Data Mikro',
    'keperluan_lain' => 'Automated test',
    'tanggal' => date('Y-m-d'),
    'waktu' => '10:00:00',
]);
test_assert($id1 > 0 && $id2 > 0, 'create() inserts buku_tamu rows');
$list = $bukuTamu->getFiltered(date('m'), date('Y'), 'Tester', 1, 20);
test_assert(count($list) >= 2, 'getFiltered() returns inserted records');
$total = $bukuTamu->getTotalFiltered(date('m'), date('Y'), 'Tester');
test_assert($total >= 2, 'getTotalFiltered() matches search results');
$stats = $bukuTamu->getStats();
test_assert($stats['total'] >= 2, 'getStats() returns aggregate counts');

echo "\n== Test 5: Kepuasan model ==\n";
$kepuasan = new Kepuasan();
$k1 = $kepuasan->create('r1@example.com', 'Sangat Puas', 'Bagus');
$k2 = $kepuasan->create('r2@example.com', 'Puas', 'Baik');
$k3 = $kepuasan->create('r3@example.com', 'Kurang Puas', 'Perlu perbaikan');
test_assert($k1 > 0 && $k2 > 0 && $k3 > 0, 'create() inserts kepuasan rows');
$kstats = $kepuasan->getStats(date('m'), date('Y'));
test_assert($kstats['total'] >= 3, 'getStats() totals kepuasan correctly');
test_assert(isset($kstats['persen_sangat_puas'], $kstats['persen_puas'], $kstats['persen_kurang_puas']), 'getStats() computes percentages');
$klist = $kepuasan->getFiltered(date('m'), date('Y'), 'Puas', 1, 20);
test_assert(count($klist) >= 1, 'getFiltered() supports rating filter');

echo "\n== Summary ==\n";
echo "Passed: {$results['passed']}\n";
echo "Failed: {$results['failed']}\n";

exit($results['failed'] > 0 ? 1 : 0);
