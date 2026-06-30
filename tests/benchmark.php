<?php
declare(strict_types=1);

/**
 * PELITA benchmark for complex query latency.
 * Run: php tests/benchmark.php
 */

$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once CLASSES_PATH . '/Database.php';

$db = Database::getInstance();
$pdo = $db->getConnection();

function ensure_seed_data(PDO $pdo, int $target = 5000): void {
    $current = (int)$pdo->query('SELECT COUNT(*) FROM buku_tamu')->fetchColumn();
    if ($current >= $target) {
        return;
    }

    $keperluan = [
        'Perpustakaan Tercetak',
        'Perpustakaan Digital',
        'Penjualan Publikasi',
        'Konsultasi Statistik',
        'Data Mikro',
    ];
    $ratings = ['Sangat Puas', 'Puas', 'Kurang Puas'];

    $pdo->beginTransaction();

    $insertBukuTamu = $pdo->prepare(
        'INSERT INTO buku_tamu
        (tahun, bulan, hari, waktu, nama, email, alamat, nohp, umur, asal, jenis_kelamin, pendidikan, pekerjaan, keperluan, keperluan_lain, nomor_antrian)
        VALUES (:tahun, :bulan, :hari, :waktu, :nama, :email, :alamat, :nohp, :umur, :asal, :jenis_kelamin, :pendidikan, :pekerjaan, :keperluan, :keperluan_lain, :nomor_antrian)'
    );

    $insertKepuasan = $pdo->prepare(
        'INSERT INTO kepuasan
        (tahun, bulan, hari, waktu, email, rating, komentar)
        VALUES (:tahun, :bulan, :hari, :waktu, :email, :rating, :komentar)'
    );

    for ($i = $current + 1; $i <= $target; $i++) {
        $month = str_pad((string)(($i % 12) + 1), 2, '0', STR_PAD_LEFT);
        $day = str_pad((string)(($i % 28) + 1), 2, '0', STR_PAD_LEFT);
        $email = "bench{$i}@example.com";

        $insertBukuTamu->execute([
            'tahun' => '2026',
            'bulan' => $month,
            'hari' => $day,
            'waktu' => sprintf('%02d:%02d:00', $i % 23, $i % 59),
            'nama' => "Benchmark User {$i}",
            'email' => $email,
            'alamat' => '-',
            'nohp' => '08' . str_pad((string)$i, 10, '0', STR_PAD_LEFT),
            'umur' => 20 + ($i % 40),
            'asal' => 'Benchmark',
            'jenis_kelamin' => ($i % 2 === 0) ? 'Laki-laki' : 'Perempuan',
            'pendidikan' => 'S1',
            'pekerjaan' => 'Tester',
            'keperluan' => $keperluan[$i % count($keperluan)],
            'keperluan_lain' => 'Synthetic benchmark data',
            'nomor_antrian' => str_pad((string)(($i % 999) + 1), 3, '0', STR_PAD_LEFT),
        ]);

        $insertKepuasan->execute([
            'tahun' => '2026',
            'bulan' => $month,
            'hari' => $day,
            'waktu' => sprintf('%02d:%02d:00', $i % 23, $i % 59),
            'email' => $email,
            'rating' => $ratings[$i % count($ratings)],
            'komentar' => null,
        ]);
    }

    $pdo->commit();
}

ensure_seed_data($pdo, 5000);

$sql = <<<SQL
SELECT
    bt.keperluan,
    COUNT(*) AS total_kunjungan,
    SUM(CASE WHEN k.rating = 'Sangat Puas' THEN 1 ELSE 0 END) AS sangat_puas,
    SUM(CASE WHEN k.rating = 'Puas' THEN 1 ELSE 0 END) AS puas,
    SUM(CASE WHEN k.rating = 'Kurang Puas' THEN 1 ELSE 0 END) AS kurang_puas
FROM buku_tamu bt
LEFT JOIN kepuasan k
    ON k.email = bt.email
    AND k.tahun = bt.tahun
    AND k.bulan = bt.bulan
WHERE bt.tahun = :tahun
GROUP BY bt.keperluan
ORDER BY total_kunjungan DESC
SQL;

$stmt = $pdo->prepare($sql);

$iterations = 30;
$samples = [];

for ($i = 0; $i < $iterations; $i++) {
    $start = microtime(true);
    $stmt->execute(['tahun' => '2026']);
    $stmt->fetchAll(PDO::FETCH_ASSOC);
    $samples[] = (microtime(true) - $start) * 1000;
}

sort($samples);
$avg = array_sum($samples) / count($samples);
$p95Index = (int)floor(count($samples) * 0.95) - 1;
$p95Index = max(0, min($p95Index, count($samples) - 1));
$p95 = $samples[$p95Index];
$min = $samples[0];
$max = $samples[count($samples) - 1];

echo "Benchmark results (ms)\n";
echo "Iterations: {$iterations}\n";
echo "Avg: " . number_format($avg, 2) . "\n";
echo "P95: " . number_format($p95, 2) . "\n";
echo "Min: " . number_format($min, 2) . "\n";
echo "Max: " . number_format($max, 2) . "\n";
