<?php
/**
 * PELITA - Export Buku Tamu to PDF (HTML)
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

// Periode text
$periode = 'Tahun ' . $tahun;
if ($bulan) {
    $periode = get_nama_bulan((int)$bulan) . ' ' . $tahun;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Buku Tamu - <?= $periode ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; padding: 20px; }
        
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #003D7A; padding-bottom: 15px; }
        .header h1 { color: #003D7A; font-size: 16px; margin-bottom: 5px; }
        .header h2 { color: #333; font-size: 14px; margin-bottom: 5px; }
        .header p { color: #666; font-size: 10px; }
        
        .info { margin-bottom: 15px; }
        .info p { margin: 3px 0; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px 4px; text-align: left; }
        th { background-color: #003D7A; color: white; font-size: 10px; }
        td { font-size: 10px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        
        .footer { margin-top: 20px; text-align: right; font-size: 10px; }
        .footer p { margin: 3px 0; }
        
        .print-btn { 
            position: fixed; top: 10px; right: 10px; 
            background: #003D7A; color: white; 
            padding: 10px 20px; border: none; 
            border-radius: 5px; cursor: pointer;
            font-size: 14px;
        }
        .print-btn:hover { background: #002855; }
        
        @media print {
            .print-btn { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>

    <div class="header">
        <h1>🔥 <?= APP_NAME ?> - <?= APP_FULL_NAME ?></h1>
        <h2>LAPORAN DATA BUKU TAMU</h2>
        <p><?= INSTITUTION_NAME ?></p>
        <p><?= INSTITUTION_ADDRESS ?></p>
    </div>

    <div class="info">
        <p><strong>Periode:</strong> <?= $periode ?></p>
        <p><strong>Total Data:</strong> <?= count($data) ?> pengunjung</p>
        <p><strong>Tanggal Cetak:</strong> <?= format_tanggal(date('Y-m-d'), 'd F Y') ?> <?= date('H:i') ?> WIB</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 70px;">Tanggal</th>
                <th>Nama</th>
                <th>Asal/Institusi</th>
                <th>Keperluan</th>
                <th style="width: 50px;">Antrian</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data)): ?>
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px;">Tidak ada data</td>
            </tr>
            <?php else: ?>
            <?php $no = 1; foreach ($data as $row): ?>
            <tr>
                <td style="text-align: center;"><?= $no++ ?></td>
                <td><?= $row['hari'] ?>/<?= $row['bulan'] ?>/<?= $row['tahun'] ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= htmlspecialchars($row['asal']) ?></td>
                <td>
                    <?= htmlspecialchars($row['keperluan']) ?>
                    <?php if ($row['keperluan_lain']): ?>
                    <br><small>(<?= htmlspecialchars($row['keperluan_lain']) ?>)</small>
                    <?php endif; ?>
                </td>
                <td style="text-align: center; font-weight: bold;"><?= $row['nomor_antrian'] ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Jember, <?= format_tanggal(date('Y-m-d'), 'd F Y') ?></p>
        <p style="margin-top: 10px;"><strong>Plt. Kepala <?= INSTITUTION_NAME ?></strong></p>
        <div style="margin-top: 70px; text-align: right;">
            <div style="display: inline-block; text-align: center;">
                <p style="margin-bottom: 5px; text-decoration: underline;"><strong>Muhamad Suharsa, SST, M.Si</strong></p>
                <p>NIP. 197608291997121001</p>
            </div>
        </div>
    </div>

</body>
</html>
