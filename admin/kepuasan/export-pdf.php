<?php
/**
 * PELITA - Export Kepuasan to PDF (HTML)
 * @version 1.0.0
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Kepuasan.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/auth.php';

require_login();

$kepuasan = new Kepuasan();

$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? date('Y');

$data = $kepuasan->getForExport($bulan ?: null, $tahun ?: null);
$stats = $kepuasan->getStats($bulan ?: null, $tahun ?: null);

$periode = 'Tahun ' . $tahun;
if ($bulan) {
    $periode = get_nama_bulan((int)$bulan) . ' ' . $tahun;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kepuasan - <?= $periode ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; padding: 20px; }
        
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #F9A825; padding-bottom: 15px; }
        .header h1 { color: #003D7A; font-size: 16px; margin-bottom: 5px; }
        .header h2 { color: #333; font-size: 14px; margin-bottom: 5px; }
        
        .stats { display: flex; justify-content: space-around; margin: 20px 0; }
        .stat-box { text-align: center; padding: 15px; border-radius: 8px; min-width: 120px; }
        .stat-box.green { background: #dcfce7; color: #166534; }
        .stat-box.yellow { background: #fef9c3; color: #854d0e; }
        .stat-box.red { background: #fee2e2; color: #991b1b; }
        .stat-box h3 { font-size: 24px; margin-bottom: 5px; }
        .stat-box p { font-size: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #F9A825; color: #003D7A; font-size: 10px; }
        
        .print-btn { 
            position: fixed; top: 10px; right: 10px; 
            background: #F9A825; color: #003D7A; 
            padding: 10px 20px; border: none; 
            border-radius: 5px; cursor: pointer;
            font-weight: bold;
        }
        
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">🖨️ Cetak PDF</button>

    <div class="header">
        <h1>🔥 <?= APP_NAME ?></h1>
        <h2>LAPORAN KEPUASAN PELANGGAN</h2>
        <p><?= INSTITUTION_NAME ?></p>
    </div>

    <p><strong>Periode:</strong> <?= $periode ?> | <strong>Total:</strong> <?= $stats['total'] ?> responden</p>

    <div class="stats">
        <div class="stat-box green">
            <h3><?= $stats['persen_sangat_puas'] ?>%</h3>
            <p>😃 Sangat Puas<br>(<?= $stats['Sangat Puas'] ?>)</p>
        </div>
        <div class="stat-box yellow">
            <h3><?= $stats['persen_puas'] ?>%</h3>
            <p>🙂 Puas<br>(<?= $stats['Puas'] ?>)</p>
        </div>
        <div class="stat-box red">
            <h3><?= $stats['persen_kurang_puas'] ?>%</h3>
            <p>😞 Kurang Puas<br>(<?= $stats['Kurang Puas'] ?>)</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Email</th>
                <th>Rating</th>
                <th>Komentar</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($data as $row): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $row['hari'] ?>/<?= $row['bulan'] ?>/<?= $row['tahun'] ?></td>
                <td><?= $row['waktu'] ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['rating'] ?></td>
                <td><?= htmlspecialchars($row['komentar'] ?? '-') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: right;">
        <p>Jember, <?= format_tanggal(date('Y-m-d'), 'd F Y') ?></p>
        <p style="margin-top: 10px;"><strong>Plt. Kepala <?= INSTITUTION_NAME ?></strong></p>
        <div style="margin-top: 70px;">
            <div style="display: inline-block; text-align: center;">
                <p style="margin-bottom: 5px; text-decoration: underline;"><strong>Muhamad Suharsa, SST, M.Si</strong></p>
                <p>NIP. 197608291997121001</p>
            </div>
        </div>
    </div>
</body>
</html>
