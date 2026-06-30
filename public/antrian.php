<?php
/**
 * PELITA - Form Pengambilan Nomor Antrian
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/AntrianController.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/csrf.php';

$controller = new AntrianController();
$errors = [];
$success = false;
$resultData = null;
$layananList = $controller->getLayananList();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $errors[] = 'Sesi tidak valid. Silakan refresh halaman.';
    } else {
        $result = $controller->ambil($_POST);

        if ($result['success']) {
            $success = true;
            $resultData = $result;
        } else {
            $errors = array_values($result['errors']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambil Antrian - <?= APP_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: {
                        'bps-blue': '#003D7A',
                        'bps-dark': '#002855',
                        'se-orange': '#F47920',
                        'se-coral': '#E85D4C',
                    }
                }
            }
        }
    </script>
    <style>
        .glass-panel {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
        }
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            outline: none;
            transition: all 0.2s;
            font-size: 0.95rem;
        }
        .form-input:focus {
            border-color: #F47920;
            box-shadow: 0 0 0 3px rgba(244, 121, 32, 0.1);
        }
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #475569;
            margin-bottom: 0.5rem;
        }
        .required::after {
            content: "*";
            color: #ef4444;
            margin-left: 2px;
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(244, 121, 32, 0.4); }
            50% { box-shadow: 0 0 0 15px rgba(244, 121, 32, 0); }
        }
        .pulse-glow { animation: pulseGlow 2s infinite; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen relative overflow-x-hidden text-slate-800">

    <!-- Background Blobs -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
        <div class="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-blue-50 to-transparent"></div>
        <div class="absolute w-[500px] h-[500px] bg-se-orange/10 top-[-100px] right-[-100px] rounded-full blur-3xl"></div>
        <div class="absolute w-[400px] h-[400px] bg-bps-blue/10 bottom-[-50px] left-[-100px] rounded-full blur-3xl"></div>
    </div>

    <!-- Header -->
    <nav class="glass-panel sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-bps-blue flex items-center justify-center shadow-lg text-white">
                    <i class="fa-solid fa-chart-simple"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-bps-blue to-se-orange pb-1">PELITA</h1>
                    <p class="text-xs text-slate-500 font-medium tracking-wider">BPS KABUPATEN JEMBER</p>
                </div>
            </div>
            <a href="<?= base_url() ?>" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition-colors">
                <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-12 max-w-3xl">

        <?php if ($success && $resultData): ?>
            <!-- Success State -->
            <div class="max-w-xl mx-auto glass-panel rounded-3xl p-10 text-center animate-fade-in">
                <div class="w-24 h-24 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-6 shadow-sm">
                    <i class="fa-solid fa-check text-4xl text-green-600"></i>
                </div>
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Berhasil!</h2>
                <p class="text-slate-500 mb-8"><?= $resultData['message'] ?></p>

                <!-- Nomor Antrian Display -->
                <div class="bg-gradient-to-br from-bps-blue to-bps-dark text-white rounded-2xl p-8 mb-6 shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl transform translate-x-1/2 -translate-y-1/2"></div>
                    <p class="text-white/70 text-sm font-medium mb-1 uppercase tracking-wider">Nomor Antrian Anda</p>
                    <p class="text-6xl font-black tracking-tight mb-2"><?= $resultData['data']['nomor_antrian'] ?></p>
                    <p class="text-white/80 text-sm"><?= sanitize($resultData['layanan']['nama']) ?></p>
                </div>

                <!-- Info Panel -->
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="bg-blue-50 rounded-xl p-4">
                        <p class="text-xs text-blue-600 font-semibold uppercase tracking-wider mb-1">Antrian Menunggu</p>
                        <p class="text-2xl font-bold text-blue-800"><?= $resultData['waiting_count'] ?> orang</p>
                    </div>
                    <div class="bg-orange-50 rounded-xl p-4">
                        <p class="text-xs text-orange-600 font-semibold uppercase tracking-wider mb-1">Estimasi Tunggu</p>
                        <p class="text-2xl font-bold text-orange-800"><?= $resultData['estimasi_menit'] ?> menit</p>
                    </div>
                </div>

                <div class="bg-slate-50 rounded-xl p-4 mb-8 text-left">
                    <div class="flex items-center gap-2 text-slate-600 text-sm mb-2">
                        <i class="fa-solid fa-circle-info text-bps-blue"></i>
                        <span class="font-semibold">Informasi Penting</span>
                    </div>
                    <ul class="text-sm text-slate-500 space-y-1 list-disc list-inside">
                        <li>Harap simpan nomor antrian Anda</li>
                        <li>Panggilan akan dilakukan oleh petugas</li>
                        <li>Harap hadir saat nomor Anda dipanggil</li>
                    </ul>
                </div>

                <div class="flex gap-4 justify-center">
                    <a href="<?= base_url() ?>" class="px-6 py-3 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition font-medium">
                        Ke Beranda
                    </a>
                    <a href="<?= base_url('public/antrian.php') ?>" class="px-6 py-3 rounded-xl bg-bps-blue text-white hover:bg-bps-dark transition shadow-lg shadow-bps-blue/30 font-medium">
                        Ambil Lagi
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- Form State -->
            <div class="text-center mb-12">
                <div class="w-20 h-20 mx-auto bg-gradient-to-br from-bps-blue to-se-orange rounded-2xl flex items-center justify-center mb-6 shadow-lg pulse-glow">
                    <i class="fa-solid fa-ticket text-3xl text-white"></i>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mb-4">Ambil Nomor Antrian</h2>
                <p class="text-slate-500 max-w-xl mx-auto text-lg">Pilih layanan yang Anda butuhkan untuk mendapatkan nomor antrian.</p>
            </div>

            <div class="glass-panel rounded-3xl p-8 md:p-10 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-bps-blue via-se-orange to-se-coral"></div>

                <?php if (!empty($errors)): ?>
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-8 flex items-start gap-3 text-red-700">
                        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                        <ul class="list-disc list-inside text-sm">
                            <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-8">
                    <?= csrf_field() ?>

                    <!-- Pilih Layanan -->
                    <div>
                        <label class="form-label required">Pilih Layanan</label>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <?php foreach ($layananList as $layanan): ?>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="kode_layanan" value="<?= $layanan['kode'] ?>"
                                       class="peer hidden" required
                                       <?= ($_POST['kode_layanan'] ?? '') === $layanan['kode'] ? 'checked' : '' ?>>
                                <div class="border-2 border-slate-200 rounded-xl p-4 transition-all duration-200
                                            peer-checked:border-bps-blue peer-checked:bg-bps-blue/5 peer-checked:shadow-md
                                            hover:border-slate-300 hover:shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-bps-blue font-bold text-sm
                                                    peer-checked:peer-checked:group-[]:bg-bps-blue peer-checked:peer-checked:group-[]:text-white">
                                            <?= $layanan['kode'] ?>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-800"><?= sanitize($layanan['nama']) ?></p>
                                            <?php if ($layanan['deskripsi']): ?>
                                                <p class="text-xs text-slate-500"><?= sanitize($layanan['deskripsi']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Informasi Pemohon (Opsional) -->
                    <div class="border-t border-slate-200 pt-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-10 h-10 rounded-full bg-blue-50 text-bps-blue flex items-center justify-center font-bold text-lg">2</div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Informasi Pemohon</h3>
                                <p class="text-sm text-slate-500">Opsional - untuk memudahkan pemanggilan</p>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="form-label">Nama Pemohon</label>
                                <div class="relative">
                                    <i class="fa-regular fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="text" name="nama_pemohon" value="<?= $_POST['nama_pemohon'] ?? '' ?>"
                                           class="form-input pl-11" placeholder="Nama (opsional)">
                                </div>
                            </div>
                            <div>
                                <label class="form-label">No. HP / WhatsApp</label>
                                <div class="relative">
                                    <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="tel" name="nohp_pemohon" value="<?= $_POST['nohp_pemohon'] ?? '' ?>"
                                           class="form-input pl-11" placeholder="08xxxxxxxxxx">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex flex-col md:flex-row gap-4 pt-4">
                        <button type="submit"
                                class="flex-1 bg-gradient-to-r from-bps-blue to-bps-dark text-white py-4 rounded-xl font-bold text-lg hover:shadow-lg hover:from-bps-dark hover:to-bps-blue transition-all transform hover:-translate-y-1">
                            <i class="fa-solid fa-ticket mr-2"></i> Ambil Nomor Antrian
                        </button>
                        <button type="reset"
                                class="px-8 py-4 rounded-xl border border-slate-200 text-slate-600 font-semibold hover:bg-slate-50 transition-colors">
                            Reset
                        </button>
                    </div>
                </form>
            </div>

            <!-- Info Layanan -->
            <div class="mt-8 glass-panel rounded-3xl p-8">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-bps-blue"></i> Informasi Layanan
                </h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <?php foreach ($layananList as $layanan): ?>
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50">
                        <div class="w-8 h-8 rounded-lg bg-bps-blue/10 flex items-center justify-center text-bps-blue text-xs font-bold flex-shrink-0 mt-0.5">
                            <?= $layanan['kode'] ?>
                        </div>
                        <div>
                            <p class="font-medium text-slate-700 text-sm"><?= sanitize($layanan['nama']) ?></p>
                            <p class="text-xs text-slate-500">Maks. <?= $layanan['max_harian'] ?>/hari</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer class="mt-12 py-8 text-center text-slate-500 text-sm">
        <p>&copy; <?= date('Y') ?> BPS Kabupaten Jember. All rights reserved.</p>
    </footer>

</body>
</html>
