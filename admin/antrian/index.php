<?php
/**
 * PELITA - Admin Antrian Management
 * @version 1.0.0
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/AntrianController.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/csrf.php';

require_login();

$controller = new AntrianController();

$kodeLayanan = $_GET['layanan'] ?? '';
$status = $_GET['status'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');
$page = max(1, (int)($_GET['page'] ?? 1));

$data = $controller->getFiltered(
    $kodeLayanan ?: null,
    $status ?: null,
    $date,
    $page
);
$total = $controller->getTotalFiltered(
    $kodeLayanan ?: null,
    $status ?: null,
    $date
);
$totalPages = ceil($total / ITEMS_PER_PAGE);
$stats = $controller->getStats($date);
$layananStats = $controller->getLayananStats($date);
$layananList = $controller->getLayananList();

$pageTitle = 'Manajemen Antrian';

include __DIR__ . '/../includes/header.php';
?>

<div class="p-6">

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><i class="fa-solid fa-ticket text-bps-blue mr-2"></i>Manajemen Antrian</h1>
            <p class="text-gray-600">Kelola antrian pelayanan hari ini</p>
        </div>
        <div class="flex gap-2 items-center">
            <input type="date" value="<?= $date ?>" id="datePicker"
                   class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-bps-blue"
                   onchange="window.location.href='?date='+this.value+'&layanan=<?= $kodeLayanan ?>&status=<?= $status ?>'">
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="glass-card rounded-xl p-4 border-l-4 border-gray-400">
            <p class="text-xs text-gray-500 font-semibold uppercase">Total</p>
            <p class="text-2xl font-bold text-gray-800"><?= $stats['total'] ?></p>
        </div>
        <div class="glass-card rounded-xl p-4 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500 font-semibold uppercase">Menunggu</p>
            <p class="text-2xl font-bold text-yellow-600"><?= $stats['menunggu'] ?></p>
        </div>
        <div class="glass-card rounded-xl p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500 font-semibold uppercase">Dipanggil</p>
            <p class="text-2xl font-bold text-blue-600"><?= $stats['dipanggil'] ?></p>
        </div>
        <div class="glass-card rounded-xl p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-500 font-semibold uppercase">Selesai</p>
            <p class="text-2xl font-bold text-green-600"><?= $stats['selesai'] ?></p>
        </div>
        <div class="glass-card rounded-xl p-4 border-l-4 border-red-400">
            <p class="text-xs text-gray-500 font-semibold uppercase">Batal</p>
            <p class="text-2xl font-bold text-red-400"><?= $stats['batal'] ?></p>
        </div>
    </div>

    <!-- Layanan Stats + Quick Actions -->
    <div class="grid lg:grid-cols-3 gap-6 mb-6">

        <!-- Layanan Status -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-bold text-gray-700 mb-4"><i class="fa-solid fa-layer-group mr-2 text-bps-blue"></i>Status Per Layanan</h3>
            <div class="space-y-3">
                <?php foreach ($layananStats as $ls): ?>
                <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl">
                    <div class="w-12 h-12 rounded-xl bg-bps-blue/10 flex items-center justify-center text-bps-blue font-bold text-sm flex-shrink-0">
                        <?= $ls['kode'] ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-semibold text-gray-800 text-sm"><?= sanitize($ls['nama']) ?></span>
                            <span class="text-xs text-gray-500"><?= $ls['total_ambil'] ?>/<?= $ls['max_harian'] ?></span>
                        </div>
                        <div class="flex gap-2 text-xs">
                            <span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full"><?= $ls['menunggu'] ?> menunggu</span>
                            <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full"><?= $ls['dipanggil'] ?> dipanggil</span>
                            <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full"><?= $ls['selesai'] ?> selesai</span>
                        </div>
                    </div>
                    <form method="POST" action="<?= base_url('admin/antrian/panggil.php') ?>" class="flex-shrink-0">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="kode_layanan" value="<?= $ls['kode'] ?>">
                        <input type="hidden" name="redirect" value="antrian">
                        <button type="submit"
                                class="bg-bps-blue text-white px-4 py-2 rounded-lg hover:bg-bps-dark transition text-sm font-medium
                                       <?= $ls['menunggu'] == 0 ? 'opacity-50 cursor-not-allowed' : '' ?>"
                                <?= $ls['menunggu'] == 0 ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-bullhorn mr-1"></i> Panggil
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Quick Info Panel -->
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-bold text-gray-700 mb-4"><i class="fa-solid fa-circle-info mr-2 text-se-orange"></i>Informasi</h3>
            <div class="space-y-4">
                <div class="bg-blue-50 rounded-xl p-4">
                    <p class="text-sm text-blue-700 font-medium">Estimasi Waktu Tunggu</p>
                    <p class="text-xs text-blue-500 mt-1">~15 menit per antrian yang diproses</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-sm text-slate-700 font-medium">Format Nomor Antrian</p>
                    <p class="text-xs text-slate-500 mt-1 font-mono">[Kode]-[Urut]-[DDMMYY]</p>
                    <p class="text-xs text-slate-500 mt-0.5">Contoh: UMU-001-300626</p>
                </div>
                <div class="bg-green-50 rounded-xl p-4">
                    <p class="text-sm text-green-700 font-medium">Cara Menggunakan</p>
                    <ol class="text-xs text-green-600 mt-1 space-y-1 list-decimal list-inside">
                        <li>Klik "Panggil" pada layanan</li>
                        <li>Sebutkan nomor antrian</li>
                        <li>Klik "Selesai" setelah pelayanan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" action="" class="flex flex-wrap gap-4 items-end">
            <input type="hidden" name="date" value="<?= $date ?>">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Layanan</label>
                <select name="layanan" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-bps-blue">
                    <option value="">Semua Layanan</option>
                    <?php foreach ($layananList as $l): ?>
                    <option value="<?= $l['kode'] ?>" <?= $kodeLayanan === $l['kode'] ? 'selected' : '' ?>><?= $l['kode'] ?> - <?= sanitize($l['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Status</label>
                <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-bps-blue">
                    <option value="">Semua Status</option>
                    <option value="menunggu" <?= $status === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                    <option value="dipanggil" <?= $status === 'dipanggil' ? 'selected' : '' ?>>Dipanggil</option>
                    <option value="selesai" <?= $status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="batal" <?= $status === 'batal' ? 'selected' : '' ?>>Batal</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-bps-blue text-white px-4 py-2 rounded-lg hover:bg-bps-blue/90 transition">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                <a href="?date=<?= $date ?>" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Reset
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nomor</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Layanan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pemohon</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            <i class="fa-solid fa-inbox text-3xl text-gray-300 block mb-2"></i>
                            Tidak ada data antrian
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php
                    $no = ($page - 1) * ITEMS_PER_PAGE + 1;
                    foreach ($data as $row):
                        $statusColors = [
                            'menunggu' => 'bg-yellow-100 text-yellow-700',
                            'dipanggil' => 'bg-blue-100 text-blue-700',
                            'selesai' => 'bg-green-100 text-green-700',
                            'batal' => 'bg-red-100 text-red-700',
                        ];
                        $statusIcons = [
                            'menunggu' => 'fa-clock',
                            'dipanggil' => 'fa-bullhorn',
                            'selesai' => 'fa-check-circle',
                            'batal' => 'fa-times-circle',
                        ];
                        $colorClass = $statusColors[$row['status']] ?? 'bg-gray-100 text-gray-700';
                        $iconClass = $statusIcons[$row['status']] ?? 'fa-circle';
                    ?>
                    <tr class="hover:bg-gray-50 <?= $row['status'] === 'dipanggil' ? 'bg-blue-50/50' : '' ?>">
                        <td class="px-4 py-3 text-sm text-gray-600"><?= $no++ ?></td>
                        <td class="px-4 py-3">
                            <span class="bg-bps-blue text-white px-3 py-1 rounded-full text-sm font-bold">
                                <?= $row['nomor_antrian'] ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="font-medium text-gray-800"><?= $row['kode_layanan'] ?></span>
                            <span class="text-gray-500 ml-1 text-xs"><?= sanitize($row['nama_layanan'] ?? '') ?></span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <?php if ($row['nama_pemohon']): ?>
                                <div class="font-medium text-gray-800"><?= sanitize($row['nama_pemohon']) ?></div>
                                <?php if ($row['nohp_pemohon']): ?>
                                    <div class="text-xs text-gray-500"><?= sanitize($row['nohp_pemohon']) ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-gray-400 text-xs">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="<?= $colorClass ?> px-2 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1">
                                <i class="fa-solid <?= $iconClass ?>"></i>
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            <div>Ambil: <?= date('H:i', strtotime($row['waktu_ambil'])) ?></div>
                            <?php if ($row['waktu_panggil']): ?>
                                <div class="text-blue-600">Panggil: <?= date('H:i', strtotime($row['waktu_panggil'])) ?></div>
                            <?php endif; ?>
                            <?php if ($row['waktu_selesai']): ?>
                                <div class="text-green-600">Selesai: <?= date('H:i', strtotime($row['waktu_selesai'])) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <?php if ($row['status'] === 'menunggu'): ?>
                                <form method="POST" action="<?= base_url('admin/antrian/panggil.php') ?>" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="redirect" value="antrian">
                                    <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded-lg text-xs hover:bg-blue-600 transition"
                                            title="Panggil">
                                        <i class="fa-solid fa-bullhorn"></i>
                                    </button>
                                </form>
                                <?php endif; ?>

                                <?php if ($row['status'] === 'dipanggil'): ?>
                                <form method="POST" action="<?= base_url('admin/antrian/selesai.php') ?>" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="redirect" value="antrian">
                                    <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded-lg text-xs hover:bg-green-600 transition"
                                            title="Selesai">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                                <form method="POST" action="<?= base_url('admin/antrian/batal.php') ?>" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="redirect" value="antrian">
                                    <button type="submit" class="bg-red-400 text-white px-3 py-1 rounded-lg text-xs hover:bg-red-500 transition"
                                            title="Batal"
                                            onclick="return confirm('Batalkan antrian ini?')">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                </form>
                                <?php endif; ?>

                                <?php if (in_array($row['status'], ['selesai', 'batal'])): ?>
                                <span class="text-gray-400 text-xs px-2">-</span>
                                <?php endif; ?>
                            </div>
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
                <a href="?page=<?= $page - 1 ?>&layanan=<?= $kodeLayanan ?>&status=<?= $status ?>&date=<?= $date ?>"
                   class="px-3 py-1 border rounded hover:bg-gray-50">&larr;</a>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                for ($i = $start; $i <= $end; $i++):
                ?>
                <a href="?page=<?= $i ?>&layanan=<?= $kodeLayanan ?>&status=<?= $status ?>&date=<?= $date ?>"
                   class="px-3 py-1 border rounded <?= $i == $page ? 'bg-bps-blue text-white' : 'hover:bg-gray-50' ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>&layanan=<?= $kodeLayanan ?>&status=<?= $status ?>&date=<?= $date ?>"
                   class="px-3 py-1 border rounded hover:bg-gray-50">&rarr;</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
