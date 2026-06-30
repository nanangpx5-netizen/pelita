<?php
/**
 * PELITA - Admin Dashboard
 * @version 2.0.0
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/BukuTamu.php';
require_once CLASSES_PATH . '/Kepuasan.php';
require_once CLASSES_PATH . '/Antrian.php';
require_once CLASSES_PATH . '/Admin.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/auth.php';

// Require login
require_login();

// Get statistics
$bukuTamu = new BukuTamu();
$kepuasan = new Kepuasan();
$antrian = new Antrian();

$statsBT = $bukuTamu->getStats();
$statsKP = $kepuasan->getStats();
$statsAntrian = $antrian->getStats();
$keperluanStats = $bukuTamu->getKeperluanStats(null, date('Y'));
$monthlyTrend = $bukuTamu->getMonthlyTrend(date('Y'));

// Page title
$pageTitle = 'Dashboard';

include __DIR__ . '/includes/header.php';
?>

<!-- Welcome Banner -->
<div class="glass-card rounded-[2rem] p-8 mb-8 relative overflow-hidden group animate-slide-up">
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-se-orange to-se-coral"></div>
    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-se-orange/10 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
    
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between relative z-10">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 mb-2">Selamat Datang, <?= admin_name() ?>! 👋</h1>
            <p class="text-slate-500 text-lg">Pantau aktifitas pelayanan BPS Kabupaten Jember secara real-time.</p>
        </div>
        <div class="mt-4 md:mt-0 text-right">
            <p class="text-sm font-semibold text-bps-blue bg-blue-50 px-4 py-2 rounded-lg inline-block">
                <?= format_tanggal(date('Y-m-d'), 'l, d F Y') ?>
            </p>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 animate-slide-up" style="animation-delay: 0.1s;">
    
    <!-- Kunjungan Hari Ini -->
    <div class="glass-card rounded-2xl p-6 border-l-4 border-se-orange">
        <div class="flex items-center justify-between mb-4">
            <span class="text-slate-500 text-sm font-semibold uppercase tracking-wider">Hari Ini</span>
            <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center text-se-orange">
                <i class="fa-solid fa-users text-lg"></i>
            </div>
        </div>
        <div class="flex items-baseline gap-2">
            <h3 class="text-3xl font-bold text-slate-800"><?= $statsBT['hari_ini'] ?></h3>
            <span class="text-xs text-slate-400">Tamu</span>
        </div>
    </div>

    <!-- Kunjungan Bulan Ini -->
    <div class="glass-card rounded-2xl p-6 border-l-4 border-se-coral">
        <div class="flex items-center justify-between mb-4">
            <span class="text-slate-500 text-sm font-semibold uppercase tracking-wider">Bulan Ini</span>
            <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center text-se-coral">
                <i class="fa-regular fa-calendar-check text-lg"></i>
            </div>
        </div>
        <div class="flex items-baseline gap-2">
            <h3 class="text-3xl font-bold text-slate-800"><?= $statsBT['bulan_ini'] ?></h3>
            <span class="text-xs text-slate-400">Tamu</span>
        </div>
    </div>

    <!-- Total Kunjungan -->
    <div class="glass-card rounded-2xl p-6 border-l-4 border-bps-blue">
        <div class="flex items-center justify-between mb-4">
            <span class="text-slate-500 text-sm font-semibold uppercase tracking-wider">Total Data</span>
            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-bps-blue">
                <i class="fa-solid fa-database text-lg"></i>
            </div>
        </div>
        <div class="flex items-baseline gap-2">
            <h3 class="text-3xl font-bold text-slate-800"><?= number_format($statsBT['total']) ?></h3>
            <span class="text-xs text-slate-400">Total Tamu</span>
        </div>
    </div>

    <!-- Total Survey -->
    <div class="glass-card rounded-2xl p-6 border-l-4 border-se-teal">
        <div class="flex items-center justify-between mb-4">
            <span class="text-slate-500 text-sm font-semibold uppercase tracking-wider">Total Survei</span>
            <div class="w-10 h-10 rounded-lg bg-teal-50 flex items-center justify-center text-se-teal">
                <i class="fa-solid fa-star text-lg"></i>
            </div>
        </div>
        <div class="flex items-baseline gap-2">
            <h3 class="text-3xl font-bold text-slate-800"><?= number_format($statsKP['total']) ?></h3>
            <span class="text-xs text-slate-400">Responden</span>
        </div>
    </div>

    <!-- Antrian Hari Ini -->
    <div class="glass-card rounded-2xl p-6 border-l-4 border-purple-500">
        <div class="flex items-center justify-between mb-4">
            <span class="text-slate-500 text-sm font-semibold uppercase tracking-wider">Antrian Hari Ini</span>
            <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600">
                <i class="fa-solid fa-ticket text-lg"></i>
            </div>
        </div>
        <div class="flex items-baseline gap-2">
            <h3 class="text-3xl font-bold text-slate-800"><?= $statsAntrian['menunggu'] ?></h3>
            <span class="text-xs text-slate-400">Menunggu</span>
        </div>
        <div class="flex gap-2 mt-2 text-xs">
            <span class="text-blue-600"><?= $statsAntrian['dipanggil'] ?> dipanggil</span>
            <span class="text-green-600"><?= $statsAntrian['selesai'] ?> selesai</span>
        </div>
    </div>

</div>

<!-- Content Grid -->
<div class="grid lg:grid-cols-2 gap-8 mb-8 animate-slide-up" style="animation-delay: 0.2s;">
    
    <!-- Kepuasan Pelanggan -->
    <div class="glass-card rounded-3xl p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-full bg-se-teal/10 flex items-center justify-center text-se-teal">
                <i class="fa-solid fa-chart-simple"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Statistik Kepuasan</h3>
        </div>
        
        <?php if ($statsKP['total'] > 0): ?>
        <div class="space-y-6">
            <!-- Sangat Puas -->
            <div>
                <div class="flex justify-between text-sm font-medium mb-2">
                    <span class="text-green-600 flex items-center gap-2"><i class="fa-regular fa-face-laugh-beam"></i> Sangat Puas</span>
                    <span class="text-slate-700"><?= $statsKP['persen_sangat_puas'] ?>%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                    <div class="bg-gradient-to-r from-green-400 to-green-600 h-3 rounded-full shadow-md" style="width: <?= $statsKP['persen_sangat_puas'] ?>%"></div>
                </div>
            </div>
            
            <!-- Puas -->
            <div>
                <div class="flex justify-between text-sm font-medium mb-2">
                    <span class="text-blue-500 flex items-center gap-2"><i class="fa-regular fa-face-smile"></i> Puas</span>
                    <span class="text-slate-700"><?= $statsKP['persen_puas'] ?>%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-3 rounded-full shadow-md" style="width: <?= $statsKP['persen_puas'] ?>%"></div>
                </div>
            </div>
            
            <!-- Kurang Puas -->
            <div>
                <div class="flex justify-between text-sm font-medium mb-2">
                    <span class="text-red-500 flex items-center gap-2"><i class="fa-regular fa-face-frown"></i> Kurang Puas</span>
                    <span class="text-slate-700"><?= $statsKP['persen_kurang_puas'] ?>%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                    <div class="bg-gradient-to-r from-red-400 to-red-600 h-3 rounded-full shadow-md" style="width: <?= $statsKP['persen_kurang_puas'] ?>%"></div>
                </div>
            </div>
        </div>
        
        <div class="mt-8 p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
            <span class="text-slate-500 font-medium">Indeks Kepuasan</span>
            <?php 
            $indeks = $statsKP['total'] > 0 
                ? round((($statsKP['Sangat Puas'] * 3 + $statsKP['Puas'] * 2 + $statsKP['Kurang Puas'] * 1) / $statsKP['total']) / 3 * 100, 1)
                : 0;
            $indeksColor = $indeks >= 80 ? 'text-green-600' : ($indeks >= 60 ? 'text-blue-600' : 'text-red-600');
            ?>
            <span class="text-3xl font-black <?= $indeksColor ?>"><?= $indeks ?>%</span>
        </div>
        <?php else: ?>
        <div class="text-center py-12">
            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-inbox text-slate-300 text-3xl"></i>
            </div>
            <p class="text-slate-500">Belum ada data survei.</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Top Keperluan -->
    <div class="glass-card rounded-3xl p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-full bg-se-orange/10 flex items-center justify-center text-se-orange">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Top Layanan</h3>
        </div>
        
        <?php if (!empty($keperluanStats)): ?>
        <div class="space-y-4">
            <?php 
            $maxJumlah = max(array_column($keperluanStats, 'jumlah'));
            $colors = ['from-blue-400 to-blue-600', 'from-orange-400 to-orange-600', 'from-teal-400 to-teal-600', 'from-purple-400 to-purple-600'];
            $i = 0;
            foreach (array_slice($keperluanStats, 0, 5) as $kp): 
                $persen = $maxJumlah > 0 ? round(($kp['jumlah'] / $maxJumlah) * 100) : 0;
                $grad = $colors[$i % count($colors)];
            ?>
            <div>
                <div class="flex justify-between text-sm mb-1.5 align-middle">
                    <span class="text-slate-600 font-medium truncate w-3/4" title="<?= $kp['keperluan'] ?>"><?= $kp['keperluan'] ?></span>
                    <span class="font-bold text-slate-800"><?= $kp['jumlah'] ?></span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2">
                    <div class="bg-gradient-to-r <?= $grad ?> h-2 rounded-full transition-all duration-1000" style="width: <?= $persen ?>%"></div>
                </div>
            </div>
            <?php $i++; endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-12">
            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-magnifying-glass text-slate-300 text-3xl"></i>
            </div>
            <p class="text-slate-500">Belum ada data kunjungan.</p>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- Quick Actions -->
<div class="glass-card rounded-2xl p-6 animate-slide-up" style="animation-delay: 0.3s;">
    <h3 class="text-lg font-bold text-slate-800 mb-4">Aksi Cepat</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="<?= base_url('admin/buku-tamu/') ?>" class="group p-4 bg-white border border-slate-100 rounded-2xl hover:border-bps-blue hover:shadow-lg transition-all text-center">
            <div class="w-12 h-12 mx-auto bg-blue-50 rounded-xl flex items-center justify-center text-bps-blue mb-3 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-table-list text-xl"></i>
            </div>
            <span class="text-sm font-semibold text-slate-600 group-hover:text-bps-blue">Data Tamu</span>
        </a>
        
        <a href="<?= base_url('admin/kepuasan/') ?>" class="group p-4 bg-white border border-slate-100 rounded-2xl hover:border-se-teal hover:shadow-lg transition-all text-center">
            <div class="w-12 h-12 mx-auto bg-teal-50 rounded-xl flex items-center justify-center text-se-teal mb-3 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-star-half-stroke text-xl"></i>
            </div>
            <span class="text-sm font-semibold text-slate-600 group-hover:text-se-teal">Data Kepuasan</span>
        </a>

        <a href="<?= base_url('admin/antrian/') ?>" class="group p-4 bg-white border border-slate-100 rounded-2xl hover:border-purple-500 hover:shadow-lg transition-all text-center">
            <div class="w-12 h-12 mx-auto bg-purple-50 rounded-xl flex items-center justify-center text-purple-600 mb-3 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-ticket text-xl"></i>
            </div>
            <span class="text-sm font-semibold text-slate-600 group-hover:text-purple-600">Antrian</span>
        </a>

        <a href="<?= base_url('admin/buku-tamu/export-excel.php') ?>" class="group p-4 bg-white border border-slate-100 rounded-2xl hover:border-green-500 hover:shadow-lg transition-all text-center">
            <div class="w-12 h-12 mx-auto bg-green-50 rounded-xl flex items-center justify-center text-green-600 mb-3 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-file-excel text-xl"></i>
            </div>
            <span class="text-sm font-semibold text-slate-600 group-hover:text-green-600">Export Excel</span>
        </a>

        <a href="<?= base_url('admin/buku-tamu/export-pdf.php') ?>" class="group p-4 bg-white border border-slate-100 rounded-2xl hover:border-red-500 hover:shadow-lg transition-all text-center">
            <div class="w-12 h-12 mx-auto bg-red-50 rounded-xl flex items-center justify-center text-red-600 mb-3 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-file-pdf text-xl"></i>
            </div>
            <span class="text-sm font-semibold text-slate-600 group-hover:text-red-600">Export PDF</span>
        </a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
