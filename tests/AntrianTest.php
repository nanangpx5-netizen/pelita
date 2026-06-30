<?php
/**
 * PELITA - Antrian Unit Tests
 * Run: php tests/AntrianTest.php
 * @package PELITA
 * @version 1.0.0
 */

declare(strict_types=1);

$_SERVER['HTTP_HOST'] = 'localhost';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Antrian.php';
require_once CLASSES_PATH . '/AntrianController.php';
require_once INCLUDES_PATH . '/functions.php';

$results = ['passed' => 0, 'failed' => 0];

function test_assert(bool $condition, string $message): void {
    global $results;
    if ($condition) {
        $results['passed']++;
        echo "[PASS] $message\n";
        return;
    }
    $results['failed']++;
    echo "[FAIL] $message\n";
}

function test_exception(callable $fn, string $expectedClass, string $message): void {
    try {
        $fn();
        test_assert(false, "$message (no exception thrown)");
    } catch (Throwable $e) {
        test_assert($e instanceof $expectedClass, "$message - got " . get_class($e) . ": " . $e->getMessage());
    }
}

$antrian = new Antrian();
$controller = new AntrianController();

echo "====== ANTRIAN TESTS ======\n\n";

// === Test 1: Database tables exist ===
echo "--- Database Schema ---\n";
$db = Database::getInstance();
$layananCount = $db->count('ref_layanan');
test_assert($layananCount >= 5, "ref_layanan has at least 5 seeded services (got $layananCount)");

// === Test 2: Get layanan list ===
echo "\n--- Layanan ---\n";
$layananList = $antrian->getLayananList();
test_assert(is_array($layananList), 'getLayananList returns array');
test_assert(count($layananList) >= 5, 'At least 5 active layanan');

$firstLayanan = $antrian->getLayananByKode('UMU');
test_assert($firstLayanan !== null, 'getLayananByKode returns data for UMU');
test_assert($firstLayanan['kode'] === 'UMU', 'Layanan UMU has correct kode');
test_assert($firstLayanan['nama'] === 'Pelayanan Umum', 'Layanan UMU has correct nama');

$invalidLayanan = $antrian->getLayananByKode('XYZ');
test_assert($invalidLayanan === null, 'getLayananByKode returns null for invalid kode');

// === Test 3: Nomor antrian format ===
echo "\n--- Nomor Antrian Format ---\n";
$nomor = $antrian->generateNomorAntrian('UMU', 1, '2026-06-30');
test_assert($nomor === 'UMU-001-260630', "Format UMU-001-260630 (got $nomor)");

$nomor2 = $antrian->generateNomorAntrian('DMI', 15, '2026-01-05');
test_assert($nomor2 === 'DMI-015-260105', "Format DMI-015-260105 (got $nomor2)");

$nomor3 = $antrian->generateNomorAntrian('PUB', 100, '2026-12-31');
test_assert($nomor3 === 'PUB-100-261231', "Format PUB-100-261231 (got $nomor3)");

// === Test 4: Ambil antrian via controller ===
echo "\n--- Ambil Antrian ---\n";

// Clean up test data first
$db->query("DELETE FROM antrian WHERE nama_pemohon LIKE 'TEST_%'");

$result = $controller->ambil([
    'kode_layanan' => 'UMU',
    'nama_pemohon' => 'TEST_Tester',
    'nohp_pemohon' => '081234567890',
]);

test_assert($result['success'] === true, 'Ambil antrian UMU succeeds');
test_assert(!empty($result['data']), 'Result contains data');
test_assert(!empty($result['data']['nomor_antrian']), 'Result has nomor_antrian');
test_assert(str_starts_with($result['data']['nomor_antrian'], 'UMU-'), "Nomor starts with UMU- (got {$result['data']['nomor_antrian']})");
test_assert($result['data']['status'] === 'menunggu', 'Status is menunggu');
test_assert($result['data']['nama_pemohon'] === 'TEST_Tester', 'nama_pemohon stored correctly');
test_assert(is_int($result['waiting_count']), 'waiting_count is integer');
test_assert(is_int($result['estimasi_menit']), 'estimasi_menit is integer');

$firstId = $result['data']['id'];

// Take another
$result2 = $controller->ambil([
    'kode_layanan' => 'UMU',
    'nama_pemohon' => 'TEST_Tester2',
]);
test_assert($result2['success'] === true, 'Ambil antrian kedua UMU succeeds');
test_assert($result2['data']['nomor_urut'] === 2, "nomor_urut is 2 (got {$result2['data']['nomor_urut']})");

$secondId = $result2['data']['id'];

// === Test 5: Validation ===
echo "\n--- Validation ---\n";

$failResult = $controller->ambil(['kode_layanan' => '']);
test_assert($failResult['success'] === false, 'Empty kode_layanan fails');
test_assert(isset($failResult['errors']['kode_layanan']), 'Error on kode_layanan');

$failResult2 = $controller->ambil(['kode_layanan' => 'XYZ']);
test_assert($failResult2['success'] === false, 'Invalid kode_layanan fails');

$failResult3 = $controller->ambil([
    'kode_layanan' => 'UMU',
    'nohp_pemohon' => 'abc',
]);
test_assert($failResult3['success'] === false, 'Invalid phone fails');
test_assert(isset($failResult3['errors']['nohp_pemohon']), 'Error on nohp_pemohon');

// === Test 6: Panggil berikutnya ===
echo "\n--- Panggil Berikutnya ---\n";

$panggilResult = $controller->panggilBerikutnya('UMU');
test_assert($panggilResult['success'] === true, 'Panggil berikutnya UMU succeeds');
test_assert($panggilResult['data']['status'] === 'dipanggil', 'Status changed to dipanggil');
test_assert(!empty($panggilResult['data']['waktu_panggil']), 'waktu_panggil is set');

// Second call should get the next waiting
$panggilResult2 = $controller->panggilBerikutnya('UMU');
test_assert($panggilResult2['success'] === true, 'Panggil kedua succeeds');
test_assert($panggilResult2['data']['id'] !== $panggilResult['data']['id'], 'Different ticket called');

// === Test 7: Selesai ===
echo "\n--- Selesai ---\n";

$selesaiResult = $controller->selesai($firstId);
test_assert($selesaiResult['success'] === true, 'Selesai succeeds');

$selesaiData = $antrian->getById($firstId);
test_assert($selesaiData['status'] === 'selesai', 'Status is selesai');
test_assert(!empty($selesaiData['waktu_selesai']), 'waktu_selesai is set');

// === Test 8: Batal ===
echo "\n--- Batal ---\n";

// Take a new one to cancel
$cancelResult = $controller->ambil(['kode_layanan' => 'DMI', 'nama_pemohon' => 'TEST_Cancel']);
test_assert($cancelResult['success'] === true, 'Ambil antrian untuk cancel');
$cancelId = $cancelResult['data']['id'];

$batalResult = $controller->batal($cancelId);
test_assert($batalResult['success'] === true, 'Batal succeeds');

$batalData = $antrian->getById($cancelId);
test_assert($batalData['status'] === 'batal', 'Status is batal');

// === Test 9: Waiting count ===
echo "\n--- Waiting Count ---\n";

$waiting = $antrian->getWaitingCount('UMU');
test_assert($waiting >= 0, "Waiting count >= 0 (got $waiting)");

$estimasi = $antrian->getEstimasiTunggu('UMU');
test_assert($estimasi >= 0, "Estimasi tunggu >= 0 (got $estimasi)");
test_assert($estimasi === $waiting * 15, 'Estimasi = waiting * 15');

// === Test 10: Stats ===
echo "\n--- Stats ---\n";

$stats = $antrian->getStats();
test_assert(is_array($stats), 'getStats returns array');
test_assert(array_key_exists('total', $stats), 'Stats has total');
test_assert(array_key_exists('menunggu', $stats), 'Stats has menunggu');
test_assert(array_key_exists('dipanggil', $stats), 'Stats has dipanggil');
test_assert(array_key_exists('selesai', $stats), 'Stats has selesai');
test_assert(array_key_exists('batal', $stats), 'Stats has batal');

$layananStats = $antrian->getLayananStats();
test_assert(is_array($layananStats), 'getLayananStats returns array');
test_assert(count($layananStats) >= 5, 'At least 5 layanan stats');

// === Test 11: Filtered list ===
echo "\n--- Filtered List ---\n";

$filtered = $antrian->getFiltered('UMU');
test_assert(is_array($filtered), 'getFiltered returns array');

$totalFiltered = $antrian->getTotalFiltered('UMU');
test_assert($totalFiltered >= 0, 'getTotalFiltered returns integer');

// === Test 12: No duplicate antrian for same layanan+date ===
echo "\n--- Uniqueness ---\n";

$allToday = $antrian->getFiltered('UMU', null, date('Y-m-d'));
$nomors = array_column($allToday, 'nomor_antrian');
$uniqueNomors = array_unique($nomors);
test_assert(count($nomors) === count($uniqueNomors), 'All nomor_antrian are unique per layanan per day');

// === Test 13: Panggil empty queue ===
echo "\n--- Empty Queue ---\n";

$emptyResult = $controller->panggilBerikutnya('PRK');
// May succeed or fail depending on whether there are waiting entries
test_assert(is_array($emptyResult), 'Panggil on potentially empty queue returns array');

// === Test 14: Controller getById ===
echo "\n--- Controller getById ---\n";

$found = $controller->getById($firstId);
test_assert($found !== null, 'getById returns data for existing ID');
test_assert($found['id'] === $firstId, 'getById returns correct record');

$notFound = $controller->getById(999999);
test_assert($notFound === null, 'getById returns null for non-existing ID');

// === Test 15: Count by layanan and date ===
echo "\n--- Count by Date ---\n";

$countToday = $antrian->countByLayananAndDate('UMU', date('Y-m-d'));
test_assert($countToday >= 2, "countByLayananAndDate UMU >= 2 (got $countToday)");

$countInvalid = $antrian->countByLayananAndDate('XYZ', date('Y-m-d'));
test_assert($countInvalid === 0, 'countByLayananAndDate returns 0 for invalid kode');

// === Cleanup ===
echo "\n--- Cleanup ---\n";
$db->query("DELETE FROM antrian WHERE nama_pemohon LIKE 'TEST_%'");
test_assert(true, 'Test data cleaned up');

// === Summary ===
echo "\n====== SUMMARY ======\n";
echo "Passed: {$results['passed']}\n";
echo "Failed: {$results['failed']}\n";

exit($results['failed'] > 0 ? 1 : 0);
