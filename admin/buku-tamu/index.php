<?php
/**
 * PELITA - Admin Buku Tamu List
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
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));

// Get data
$data = $bukuTamu->getFiltered($bulan ?: null, $tahun ?: null, $search ?: null, $page);
$total = $bukuTamu->getTotalFiltered($bulan ?: null, $tahun ?: null, $search ?: null);
$totalPages = ceil($total / ITEMS_PER_PAGE);

// Page title
$pageTitle = 'Data Buku Tamu';

include __DIR__ . '/../includes/header.php';
?>

<div class="p-6">
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📋 Data Buku Tamu</h1>
            <p class="text-gray-600">Total: <?= number_format($total) ?> data</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= base_url('admin/buku-tamu/export-excel.php') ?>?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" 
               class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                <span>📊</span> Export Excel
            </a>
            <a href="<?= base_url('admin/buku-tamu/export-pdf.php') ?>?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" 
               class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition flex items-center gap-2">
                <span>📄</span> Export PDF
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" action="" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Bulan</label>
                <select name="bulan" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-bps-blue">
                    <option value="">Semua Bulan</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= $bulan == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>>
                        <?= get_nama_bulan($m) ?>
                    </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Tahun</label>
                <select name="tahun" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-bps-blue">
                    <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                    <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm text-gray-600 mb-1">Cari</label>
                <input type="text" name="search" value="<?= sanitize($search) ?>" 
                       placeholder="Nama, email, atau asal..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-bps-blue">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-bps-blue text-white px-4 py-2 rounded-lg hover:bg-bps-blue/90 transition">
                    🔍 Filter
                </button>
                <a href="<?= base_url('admin/buku-tamu/') ?>" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
                    ↺ Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal/Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keperluan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Antrian</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sync</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                            <span class="text-4xl block mb-2">📭</span>
                            Tidak ada data ditemukan
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php 
                    $no = ($page - 1) * ITEMS_PER_PAGE + 1;
                    foreach ($data as $row): 
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-600"><?= $no++ ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            <?= $row['hari'] ?>/<?= $row['bulan'] ?>/<?= $row['tahun'] ?><br>
                            <span class="text-xs text-gray-400"><?= $row['waktu'] ?></span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900"><?= sanitize($row['nama']) ?></div>
                            <div class="text-xs text-gray-500"><?= $row['jenis_kelamin'] ?>, <?= $row['umur'] ?> th</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600"><?= sanitize($row['email']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600 max-w-[150px] truncate" title="<?= sanitize($row['asal']) ?>">
                            <?= sanitize($row['asal']) ?>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                <?= sanitize($row['keperluan']) ?>
                            </span>
                            <?php if ($row['keperluan_lain']): ?>
                            <div class="text-xs text-gray-500 mt-1"><?= sanitize($row['keperluan_lain']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="bg-bps-blue text-white px-3 py-1 rounded-full text-sm font-bold">
                                <?= $row['nomor_antrian'] ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            <?php if (!empty($row['synced_at'])): ?>
                                <span class="text-green-500" title="Synced: <?= $row['synced_at'] ?>">
                                    <i class="fa-solid fa-cloud-check"></i>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-300" title="Belum sinkron">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="px-4 py-3 border-t flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Menampilkan <?= (($page - 1) * ITEMS_PER_PAGE) + 1 ?> - <?= min($page * ITEMS_PER_PAGE, $total) ?> dari <?= $total ?>
            </div>
            <div class="flex gap-1">
                <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&search=<?= urlencode($search) ?>" 
                   class="px-3 py-1 border rounded hover:bg-gray-50">←</a>
                <?php endif; ?>
                
                <?php 
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                
                for ($i = $start; $i <= $end; $i++): 
                ?>
                <a href="?page=<?= $i ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&search=<?= urlencode($search) ?>" 
                   class="px-3 py-1 border rounded <?= $i == $page ? 'bg-bps-blue text-white' : 'hover:bg-gray-50' ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&search=<?= urlencode($search) ?>" 
                   class="px-3 py-1 border rounded hover:bg-gray-50">→</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
