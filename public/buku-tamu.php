<?php
/**
 * PELITA - Form Buku Tamu
 * @version 1.1.0
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/BukuTamuController.php'; // Use Controller
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/csrf.php';

$errors = [];
$success = false;
$nomorAntrian = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validate CSRF
    if (!validate_csrf()) {
        $errors[] = 'Sesi tidak valid. Silakan refresh halaman.';
    } else {
        // Instantiate Controller
        $controller = new BukuTamuController();
        
        // Process Store
        $result = $controller->store($_POST);
        
        if ($result['success']) {
            $success = true;
            $nomorAntrian = $result['nomor_antrian'];
        } else {
            // Flatten errors for display
            $errors = array_values($result['errors']);
        }
    }
}

// Get reference data
$refKeperluan = get_ref_data('ref_keperluan');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tamu - <?= APP_NAME ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
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

    <main class="container mx-auto px-4 py-12 max-w-5xl">
        
        <?php if ($success): ?>
            <div class="max-w-xl mx-auto glass-panel rounded-3xl p-10 text-center animate-fade-in">
                <div class="w-24 h-24 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-6 shadow-sm">
                    <i class="fa-solid fa-check text-4xl text-green-600"></i>
                </div>
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Berhasil!</h2>
                <p class="text-slate-500 mb-8">Data kunjungan Anda telah tersimpan.</p>
                
                <div class="bg-gradient-to-br from-se-orange to-se-coral text-white rounded-2xl p-8 mb-8 shadow-xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl transform translate-x-1/2 -translate-y-1/2"></div>
                    <p class="text-white/80 text-sm font-medium mb-1 uppercase tracking-wider">Nomor Antrian Anda</p>
                    <p class="text-6xl font-bold tracking-tight"><?= $nomorAntrian ?></p>
                </div>
                
                <div class="flex gap-4 justify-center">
                    <a href="<?= base_url() ?>" class="px-6 py-3 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition font-medium">
                        Ke Beranda
                    </a>
                    <a href="<?= base_url('public/buku-tamu.php') ?>" class="px-6 py-3 rounded-xl bg-bps-blue text-white hover:bg-bps-dark transition shadow-lg shadow-bps-blue/30 font-medium">
                        Isi Lagi
                    </a>
                </div>
            </div>
        <?php else: ?>

            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mb-4">Formulir Buku Tamu</h2>
                <p class="text-slate-500 max-w-2xl mx-auto text-lg">Silakan lengkapi data kunjungan Anda untuk mendapatkan pelayanan terbaik dari kami.</p>
            </div>

            <div class="glass-panel rounded-3xl p-8 md:p-10 relative overflow-hidden">
                <!-- Decorative bar -->
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

                <form action="" method="POST" class="space-y-10">
                    <?= csrf_field() ?>

                    <!-- SECTION 1: VISIT DATA (Data Kunjungan) -->
                    <div>
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-10 h-10 rounded-full bg-orange-50 text-se-orange flex items-center justify-center font-bold text-lg">1</div>
                            <h3 class="text-xl font-bold text-slate-800">Data Kunjungan</h3>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Tanggal (Otomatis) -->
                            <div>
                                <label class="form-label">Tanggal Kunjungan</label>
                                <div class="form-input bg-slate-100 text-slate-600">
                                    <?= date('d F Y') ?>
                                </div>
                                <input type="hidden" name="tanggal_kunjungan" value="<?= date('Y-m-d') ?>">
                            </div>
                            <!-- Tujuan -->
                            <div>
                                <label class="form-label required">Tujuan Kunjungan</label>
                                <select name="keperluan" class="form-input bg-white" required>
                                    <option value="">-- Pilih Tujuan --</option>
                                    <?php foreach ($refKeperluan as $k): ?>
                                        <option value="<?= $k['nama'] ?>" <?= ($_POST['keperluan'] ?? '') === $k['nama'] ? 'selected' : '' ?>>
                                            <?= $k['nama'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- Orang Ditemui -->
                            <div class="col-span-2 md:col-span-1">
                                <label class="form-label">Orang yang ingin ditemui</label>
                                <div class="relative">
                                    <i class="fa-solid fa-user-tie absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="text" name="orang_ditemui" value="<?= $_POST['orang_ditemui'] ?? '' ?>" class="form-input pl-11" placeholder="Nama pegawai (Opsional)">
                                </div>
                            </div>

                            
                             <!-- Detail -->
                             <div class="col-span-2">
                                <label class="form-label">Detail Keperluan</label>
                                <textarea name="rincian" rows="3" class="form-input resize-none" placeholder="Jelaskan secara singkat keperluan Anda..."><?= $_POST['rincian'] ?? '' ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-200"></div>

                    <!-- SECTION 2: VISITOR INFO (Informasi Pengunjung) -->
                    <div>
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-10 h-10 rounded-full bg-blue-50 text-bps-blue flex items-center justify-center font-bold text-lg">2</div>
                            <h3 class="text-xl font-bold text-slate-800">Informasi Pengunjung</h3>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Nama -->
                            <div class="col-span-2 md:col-span-1">
                                <label class="form-label required">Nama Lengkap</label>
                                <div class="relative">
                                    <i class="fa-regular fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="text" name="nama" value="<?= $_POST['nama'] ?? '' ?>" class="form-input pl-11" placeholder="Masukkan nama lengkap" required>
                                </div>
                            </div>
                            <!-- Instansi -->
                            <div class="col-span-2 md:col-span-1">
                                <label class="form-label required">Instansi / Perusahaan</label>
                                <div class="relative">
                                    <i class="fa-regular fa-building absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="text" name="instansi" value="<?= $_POST['instansi'] ?? '' ?>" class="form-input pl-11" placeholder="Nama instansi atau 'Umum'" required>
                                </div>
                            </div>
                            <!-- No HP -->
                            <div>
                                <label class="form-label required">Nomor Telepon / WhatsApp</label>
                                <div class="relative">
                                    <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="tel" name="nohp" value="<?= $_POST['nohp'] ?? '' ?>" class="form-input pl-11" placeholder="08xxxxxxxxxx" required>
                                </div>
                            </div>
                            <!-- Email -->
                            <div>
                                <label class="form-label">Alamat Email</label>
                                <div class="relative">
                                    <i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="email" name="email" value="<?= $_POST['email'] ?? '' ?>" class="form-input pl-11" placeholder="email@contoh.com">
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- ACTIONS -->
                    <div class="flex flex-col md:flex-row gap-4 pt-4">
                        <button type="submit" class="flex-1 bg-gradient-to-r from-se-orange to-se-coral text-white py-4 rounded-xl font-bold text-lg hover:shadow-lg hover:from-orange-600 hover:to-red-500 transition-all transform hover:-translate-y-1">
                            Submit Buku Tamu <i class="fa-solid fa-paper-plane ml-2"></i>
                        </button>
                        <button type="reset" class="px-8 py-4 rounded-xl border border-slate-200 text-slate-600 font-semibold hover:bg-slate-50 transition-colors">
                            Reset
                        </button>
                    </div>

                </form>
            </div>
        <?php endif; ?>
    </main>

    <footer class="mt-12 py-8 text-center text-slate-500 text-sm">
        <p>&copy; <?= date('Y') ?> BPS Kabupaten Jember. All rights reserved.</p>
    </footer>

</body>
</html>
