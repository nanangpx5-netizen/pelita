<?php
/**
 * PELITA - Export Buku Tamu to Excel
 * @version 1.0.0
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/BukuTamu.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/auth.php';

require_login();

$bukuTamu = new BukuTamu();

// Get filter parameters
$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? date('Y');

// Get data
$data = $bukuTamu->getForExport($bulan ?: null, $tahun ?: null);

// Generate filename
$filename = 'BukuTamu_PELITA';
if ($bulan) $filename .= '_' . get_nama_bulan((int)$bulan);
if ($tahun) $filename .= '_' . $tahun;
$filename .= '_' . date('Ymd_His') . '.xls';

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');
?>
<html>
<head>
<meta charset="UTF-8">
<style>
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #000; padding: 8px; text-align: left; }
    th { background-color: #003D7A; color: white; font-weight: bold; }
    tr:nth-child(even) { background-color: #f2f2f2; }
    .title { font-size: 16pt; font-weight: bold; margin-bottom: 10px; }
    .subtitle { font-size: 10pt; color: #666; margin-bottom: 20px; }
</style>
</head>
<body>
<div class="title">Data Buku Tamu - PELITA</div>
<div class="subtitle">
    BPS Kabupaten Jember | 
    <?php if ($bulan): ?>Bulan: <?= get_nama_bulan((int)$bulan) ?> <?php endif; ?>
    <?php if ($tahun): ?>Tahun: <?= $tahun ?><?php endif; ?> | 
    Diekspor: <?= date('d/m/Y H:i:s') ?>
</div>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Waktu</th>
            <th>Nama</th>
            <th>Email</th>
            <th>No HP</th>
            <th>Alamat</th>
            <th>Umur</th>
            <th>Jenis Kelamin</th>
            <th>Asal/Institusi</th>
            <th>Pendidikan</th>
            <th>Pekerjaan</th>
            <th>Keperluan</th>
            <th>Keperluan Lain</th>
            <th>No Antrian</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($data)): ?>
        <tr>
            <td colspan="15" style="text-align: center;">Tidak ada data</td>
        </tr>
        <?php else: ?>
        <?php $no = 1; foreach ($data as $row): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $row['hari'] ?>/<?= $row['bulan'] ?>/<?= $row['tahun'] ?></td>
            <td><?= $row['waktu'] ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['nohp']) ?></td>
            <td><?= htmlspecialchars($row['alamat'] ?? '-') ?></td>
            <td><?= $row['umur'] ?></td>
            <td><?= $row['jenis_kelamin'] ?></td>
            <td><?= htmlspecialchars($row['asal']) ?></td>
            <td><?= htmlspecialchars($row['pendidikan']) ?></td>
            <td><?= htmlspecialchars($row['pekerjaan']) ?></td>
            <td><?= htmlspecialchars($row['keperluan']) ?></td>
            <td><?= htmlspecialchars($row['keperluan_lain'] ?? '-') ?></td>
            <td><?= $row['nomor_antrian'] ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
</body>
</html>

