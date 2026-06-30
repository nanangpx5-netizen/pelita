<?php
/**
 * PELITA - Admin Kepuasan List
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

// Get filter parameters
$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? date('Y');
$rating = $_GET['rating'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));

// Get data
$data = $kepuasan->getFiltered($bulan ?: null, $tahun ?: null, $rating ?: null, $page);
$total = $kepuasan->getTotalFiltered($bulan ?: null, $tahun ?: null, $rating ?: null);
$totalPages = ceil($total / ITEMS_PER_PAGE);
$stats = $kepuasan->getStats($bulan ?: null, $tahun ?: null);

// Page title
$pageTitle = 'Data Kepuasan Pelanggan';

include __DIR__ . '/../includes/header.php';
?>

<div class="p-6">
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">⭐ Data Kepuasan Pelanggan</h1>
            <p class="text-gray-600">Total: <?= number_format($stats['total']) ?> data</p>
        </div>
        <a href="<?= base_url('admin/kepuasan/export-pdf.php') ?>?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" 
           class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition flex items-center gap-2 w-fit">
            <span>📄</span> Export PDF
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">😃 Sangat Puas</p>
            <p class="text-2xl font-bold text-gray-800"><?= $stats['Sangat Puas'] ?></p>
            <p class="text-xs text-green-600"><?= $stats['persen_sangat_puas'] ?>%</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-500">
            <p class="text-sm text-gray-500">🙂 Puas</p>
            <p class="text-2xl font-bold text-gray-800"><?= $stats['Puas'] ?></p>
            <p class="text-xs text-yellow-600"><?= $stats['persen_puas'] ?>%</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-red-500">
            <p class="text-sm text-gray-500">😞 Kurang Puas</p>
            <p class="text-2xl font-bold text-gray-800"><?= $stats['Kurang Puas'] ?></p>
            <p class="text-xs text-red-600"><?= $stats['persen_kurang_puas'] ?>%</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">📊 Total Survey</p>
            <p class="text-2xl font-bold text-gray-800"><?= $stats['total'] ?></p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" action="" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Bulan</label>
                <select name="bulan" class="border border-gray-300 rounded-lg px-3 py-2">
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
                <select name="tahun" class="border border-gray-300 rounded-lg px-3 py-2">
                    <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                    <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Rating</label>
                <select name="rating" class="border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Semua Rating</option>
                    <option value="Sangat Puas" <?= $rating === 'Sangat Puas' ? 'selected' : '' ?>>😃 Sangat Puas</option>
                    <option value="Puas" <?= $rating === 'Puas' ? 'selected' : '' ?>>🙂 Puas</option>
                    <option value="Kurang Puas" <?= $rating === 'Kurang Puas' ? 'selected' : '' ?>>😞 Kurang Puas</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-bps-blue text-white px-4 py-2 rounded-lg hover:bg-bps-blue/90 transition">
                    🔍 Filter
                </button>
                <a href="<?= base_url('admin/kepuasan/') ?>" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Komentar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            <span class="text-4xl block mb-2">📭</span>
                            Tidak ada data ditemukan
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php 
                    $no = ($page - 1) * ITEMS_PER_PAGE + 1;
                    foreach ($data as $row): 
                        $ratingEmoji = match($row['rating']) {
                            'Sangat Puas' => '😃',
                            'Puas' => '🙂',
                            'Kurang Puas' => '😞',
                            default => '❓'
                        };
                        $ratingColor = match($row['rating']) {
                            'Sangat Puas' => 'bg-green-100 text-green-800',
                            'Puas' => 'bg-yellow-100 text-yellow-800',
                            'Kurang Puas' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-600"><?= $no++ ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            <?= $row['hari'] ?>/<?= $row['bulan'] ?>/<?= $row['tahun'] ?><br>
                            <span class="text-xs text-gray-400"><?= $row['waktu'] ?></span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600"><?= sanitize($row['email']) ?></td>
                        <td class="px-4 py-3">
                            <span class="<?= $ratingColor ?> px-3 py-1 rounded-full text-sm">
                                <?= $ratingEmoji ?> <?= $row['rating'] ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 max-w-[200px]">
                            <?= $row['komentar'] ? sanitize($row['komentar']) : '<span class="text-gray-400 italic">-</span>' ?>
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
                <a href="?page=<?= $page - 1 ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&rating=<?= urlencode($rating) ?>" 
                   class="px-3 py-1 border rounded hover:bg-gray-50">←</a>
                <?php endif; ?>
                
                <?php 
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                
                for ($i = $start; $i <= $end; $i++): 
                ?>
                <a href="?page=<?= $i ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&rating=<?= urlencode($rating) ?>" 
                   class="px-3 py-1 border rounded <?= $i == $page ? 'bg-bps-blue text-white' : 'hover:bg-gray-50' ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&rating=<?= urlencode($rating) ?>" 
                   class="px-3 py-1 border rounded hover:bg-gray-50">→</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
