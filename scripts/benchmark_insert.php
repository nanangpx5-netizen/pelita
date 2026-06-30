<?php
/**
 * PELITA - Performance Benchmark: Bulk Insert 1000 Records
 * Target: < 2 detik untuk 1000 record di SQLite lokal
 * Usage: php scripts/benchmark_insert.php
 */

require_once __DIR__ . '/../config/app.php';
require_once CLASSES_PATH . '/Database.php';

echo "====== PELITA PERFORMANCE BENCHMARK ======\n";
echo "DB Type: " . DB_TYPE . "\n";
echo "Target: 1000 records < 2 seconds\n\n";

$db = Database::getInstance();
$pdo = $db->getConnection();

$record_count = 1000;

// Helper: generate random buku_tamu data
function generate_random_record(int $i): array
{
    $names = ['Ahmad', 'Budi', 'Citra', 'Dewi', 'Eka', 'Fajar', 'Gita', 'Hadi', 'Indah', 'Joko'];
    $kelamin = ['Laki-laki', 'Perempuan'];
    $pendidikan = ['SD', 'SMP', 'SMA/SMK', 'D1/D2/D3', 'D4/S1', 'S2', 'S3'];
    $pekerjaan = ['Mahasiswa', 'PNS', 'Karyawan Swasta', 'Wiraswasta'];
    $keperluan = ['Perpustakaan Tercetak', 'Perpustakaan Digital', 'Konsultasi Statistik', 'Data Mikro'];

    return [
        'tahun' => 2026,
        'bulan' => str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT),
        'hari' => str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT),
        'waktu' => sprintf('%02d:%02d:%02d', rand(8, 16), rand(0, 59), rand(0, 59)),
        'nama' => $names[array_rand($names)] . ' Test ' . $i,
        'email' => "test{$i}@benchmark.test",
        'alamat' => "Jl. Test No. $i, Jember",
        'nohp' => '08' . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
        'umur' => rand(18, 65),
        'asal' => 'Benchmark Test',
        'jenis_kelamin' => $kelamin[array_rand($kelamin)],
        'pendidikan' => $pendidikan[array_rand($pendidikan)],
        'pekerjaan' => $pekerjaan[array_rand($pekerjaan)],
        'keperluan' => $keperluan[array_rand($keperluan)],
        'keperluan_lain' => null,
        'nomor_antrian' => sprintf('T%03d', $i),
    ];
}

// ===== BENCHMARK 1: Individual Inserts =====
echo "--- Test 1: Individual INSERTs ($record_count records) ---\n";

$columns = [
    'tahun',
    'bulan',
    'hari',
    'waktu',
    'nama',
    'email',
    'alamat',
    'nohp',
    'umur',
    'asal',
    'jenis_kelamin',
    'pendidikan',
    'pekerjaan',
    'keperluan',
    'keperluan_lain',
    'nomor_antrian'
];
$colNames = implode(', ', array_map(fn($c) => "`$c`", $columns));
$placeholders = implode(', ', array_map(fn($c) => ":$c", $columns));
$sql = "INSERT INTO `buku_tamu` ($colNames) VALUES ($placeholders)";
$stmt = $pdo->prepare($sql);

$start = microtime(true);

for ($i = 1; $i <= $record_count; $i++) {
    $data = generate_random_record($i);
    $stmt->execute($data);
}

$elapsed1 = microtime(true) - $start;
$perRecord1 = ($elapsed1 / $record_count) * 1000; // ms

printf("Result: %.3f seconds (%.2f ms/record)\n", $elapsed1, $perRecord1);
echo ($elapsed1 < 2.0 ? "✅ PASS" : "❌ FAIL") . " (target < 2.0s)\n\n";

// Cleanup test 1
$pdo->exec("DELETE FROM `buku_tamu` WHERE `asal` = 'Benchmark Test'");

// ===== BENCHMARK 2: Transaction-wrapped Batch Insert =====
echo "--- Test 2: Transaction Batch INSERT ($record_count records) ---\n";

$start = microtime(true);
$pdo->beginTransaction();

for ($i = 1; $i <= $record_count; $i++) {
    $data = generate_random_record($i);
    $stmt->execute($data);
}

$pdo->commit();
$elapsed2 = microtime(true) - $start;
$perRecord2 = ($elapsed2 / $record_count) * 1000;

printf("Result: %.3f seconds (%.2f ms/record)\n", $elapsed2, $perRecord2);
echo ($elapsed2 < 2.0 ? "✅ PASS" : "❌ FAIL") . " (target < 2.0s)\n\n";

// Cleanup test 2
$pdo->exec("DELETE FROM `buku_tamu` WHERE `asal` = 'Benchmark Test'");

// ===== SUMMARY =====
echo "====== SUMMARY ======\n";
printf("Individual INSERTs: %.3fs %s\n", $elapsed1, $elapsed1 < 2.0 ? '✅' : '❌');
printf("Transaction Batch:  %.3fs %s\n", $elapsed2, $elapsed2 < 2.0 ? '✅' : '❌');
printf("Speedup: %.1fx faster with transactions\n", $elapsed1 / max($elapsed2, 0.001));
echo "\n";
