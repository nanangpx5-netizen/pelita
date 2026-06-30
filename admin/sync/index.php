<?php
/**
 * PELITA - Sync Monitoring Dashboard
 * @version 1.0.0
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/SyncManager.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/auth.php';

require_login();

$manager = new SyncManager();
$pending = $manager->getPendingCount();
$lastSync = $manager->getLastSyncTime();
$connected = $manager->isConnected();

// Test connection details
$connTest = $connected ? $manager->testConnection() : null;

// Recent logs
$recentLogs = $manager->readRecentLogs(30);

// Flash message from manual sync
$syncResult = $_SESSION['sync_result'] ?? null;
unset($_SESSION['sync_result']);

$totalPending = array_sum(array_filter($pending, fn($v) => $v >= 0));

$pageTitle = 'Monitoring Sinkronisasi';

include __DIR__ . '/../includes/header.php';
?>

<div class="p-6 space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🔄 Monitoring Sinkronisasi</h1>
            <p class="text-gray-600">Status sync data lokal ke hosting cloud</p>
        </div>
        <form method="POST" action="<?= base_url('admin/sync/run.php') ?>">
            <button type="submit"
                class="bg-bps-blue text-white px-6 py-3 rounded-xl hover:bg-bps-dark transition flex items-center gap-2 font-semibold shadow-lg">
                <i class="fa-solid fa-rotate"></i> Sync Sekarang
            </button>
        </form>
    </div>

    <!-- Flash Message -->
    <?php if ($syncResult): ?>
        <div
            class="rounded-xl p-4 <?= $syncResult['status'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' ?>">
            <div class="flex items-center gap-3">
                <i
                    class="fa-solid <?= $syncResult['status'] ? 'fa-check-circle text-green-500' : 'fa-exclamation-circle text-red-500' ?> text-xl"></i>
                <div>
                    <?php if ($syncResult['status']): ?>
                        <p class="font-semibold text-green-800">Sync berhasil!</p>
                        <p class="text-sm text-green-600">
                            Buku Tamu:
                            <?= $syncResult['stats']['buku_tamu'] ?? 0 ?> |
                            Kepuasan:
                            <?= $syncResult['stats']['kepuasan'] ?? 0 ?> |
                            Skipped:
                            <?= $syncResult['stats']['skipped'] ?? 0 ?> |
                            Conflicts:
                            <?= $syncResult['stats']['conflicts'] ?? 0 ?>
                        </p>
                    <?php else: ?>
                        <p class="font-semibold text-red-800">Sync gagal</p>
                        <p class="text-sm text-red-600">
                            <?= $syncResult['message'] ?? 'Unknown error' ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Connection Status -->
        <div class="glass-card rounded-2xl p-6 border-l-4 <?= $connected ? 'border-green-500' : 'border-red-500' ?>">
            <div class="flex items-center justify-between mb-3">
                <span class="text-slate-500 text-sm font-semibold uppercase">Koneksi Remote</span>
                <div
                    class="w-8 h-8 rounded-full <?= $connected ? 'bg-green-100' : 'bg-red-100' ?> flex items-center justify-center">
                    <i class="fa-solid <?= $connected ? 'fa-link text-green-500' : 'fa-link-slash text-red-500' ?>"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold <?= $connected ? 'text-green-600' : 'text-red-600' ?>">
                <?= $connected ? 'Connected' : 'Disconnected' ?>
            </h3>
            <?php if ($connTest && $connTest['status']): ?>
                <p class="text-xs text-slate-400 mt-1">
                    <?= $connTest['host'] ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Pending Count -->
        <div
            class="glass-card rounded-2xl p-6 border-l-4 <?= $totalPending > 0 ? 'border-se-orange' : 'border-green-500' ?>">
            <div class="flex items-center justify-between mb-3">
                <span class="text-slate-500 text-sm font-semibold uppercase">Pending Sync</span>
                <div
                    class="w-8 h-8 rounded-full <?= $totalPending > 0 ? 'bg-orange-100' : 'bg-green-100' ?> flex items-center justify-center">
                    <i
                        class="fa-solid <?= $totalPending > 0 ? 'fa-clock text-se-orange' : 'fa-check text-green-500' ?>"></i>
                </div>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">
                <?= $totalPending ?>
            </h3>
            <p class="text-xs text-slate-400 mt-1">
                BT:
                <?= $pending['buku_tamu'] ?? 0 ?> | KP:
                <?= $pending['kepuasan'] ?? 0 ?>
            </p>
        </div>

        <!-- Last Sync -->
        <div class="glass-card rounded-2xl p-6 border-l-4 border-bps-blue">
            <div class="flex items-center justify-between mb-3">
                <span class="text-slate-500 text-sm font-semibold uppercase">Sync Terakhir</span>
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fa-solid fa-clock-rotate-left text-bps-blue"></i>
                </div>
            </div>
            <h3 class="text-lg font-bold text-slate-800">
                <?= $lastSync ? date('d/m/Y H:i', strtotime($lastSync)) : 'Belum pernah' ?>
            </h3>
            <?php if ($lastSync): ?>
                <p class="text-xs text-slate-400 mt-1">
                    <?= time_ago($lastSync) ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Remote Data -->
        <div class="glass-card rounded-2xl p-6 border-l-4 border-se-teal">
            <div class="flex items-center justify-between mb-3">
                <span class="text-slate-500 text-sm font-semibold uppercase">Data di Remote</span>
                <div class="w-8 h-8 rounded-full bg-teal-100 flex items-center justify-center">
                    <i class="fa-solid fa-cloud text-se-teal"></i>
                </div>
            </div>
            <?php if ($connTest && $connTest['status']): ?>
                <h3 class="text-3xl font-bold text-slate-800">
                    <?= array_sum(array_column($connTest['tables'], 'count')) ?>
                </h3>
                <p class="text-xs text-slate-400 mt-1">
                    BT:
                    <?= $connTest['tables']['buku_tamu']['count'] ?? '?' ?> |
                    KP:
                    <?= $connTest['tables']['kepuasan']['count'] ?? '?' ?>
                </p>
            <?php else: ?>
                <h3 class="text-lg font-bold text-red-500">N/A</h3>
                <p class="text-xs text-slate-400 mt-1">Tidak bisa terhubung</p>
            <?php endif; ?>
        </div>

    </div>

    <!-- Detail Tables -->
    <div class="grid lg:grid-cols-2 gap-6">

        <!-- Sync Configuration -->
        <div class="glass-card rounded-2xl p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-gear text-slate-400"></i> Konfigurasi
            </h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-slate-500">Mode</span>
                    <span class="font-semibold">One-Way (Local → Cloud)</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-slate-500">Local DB</span>
                    <span class="font-mono text-xs bg-slate-100 px-2 py-1 rounded">
                        <?= DB_TYPE ?>
                    </span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-slate-500">Remote Host</span>
                    <span class="font-mono text-xs bg-slate-100 px-2 py-1 rounded">
                        <?= getenv('REMOTE_DB_HOST') ?: '-' ?>
                    </span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-slate-500">Remote DB</span>
                    <span class="font-mono text-xs bg-slate-100 px-2 py-1 rounded">
                        <?= getenv('REMOTE_DB_NAME') ?: '-' ?>
                    </span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-slate-500">Batch Size</span>
                    <span class="font-semibold">50 records/cycle</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Conflict Detection</span>
                    <span class="text-green-600 font-semibold"><i class="fa-solid fa-shield-check"></i> Aktif
                        (SHA-256)</span>
                </div>
            </div>
        </div>

        <!-- Sync Log -->
        <div class="glass-card rounded-2xl p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-list-ul text-slate-400"></i> Log Aktivitas (Hari Ini)
            </h3>
            <div class="max-h-[320px] overflow-y-auto space-y-1">
                <?php if (empty($recentLogs)): ?>
                    <div class="text-center py-8">
                        <i class="fa-solid fa-inbox text-slate-300 text-3xl mb-2 block"></i>
                        <p class="text-slate-400">Belum ada aktivitas sync hari ini</p>
                    </div>
                <?php else: ?>
                    <?php foreach (array_reverse($recentLogs) as $logLine): ?>
                        <div class="text-xs font-mono py-1.5 px-2 rounded <?=
                            str_contains($logLine, 'Error') || str_contains($logLine, 'CONFLICT') ? 'bg-red-50 text-red-700' :
                            (str_contains($logLine, 'Connected') || str_contains($logLine, 'completed') ? 'bg-green-50 text-green-700' :
                                'bg-slate-50 text-slate-600')
                            ?>">
                            <?= htmlspecialchars($logLine) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>