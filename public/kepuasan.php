<?php
/**
 * PELITA - Form Kepuasan Pelanggan
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Kepuasan.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/csrf.php';

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validate CSRF
    if (!validate_csrf()) {
        $errors[] = 'Sesi tidak valid. Silakan refresh halaman.';
    } else {
        // Sanitize input
        $email = sanitize($_POST['email'] ?? '');
        $rating = $_POST['rating'] ?? '';
        $komentar = sanitize($_POST['komentar'] ?? '');
        
        // Validation
        if (!empty($email) && !validate_email($email)) {
            $errors[] = 'Format email tidak valid';
        }
        
        if (!in_array($rating, ['Sangat Puas', 'Puas', 'Kurang Puas'])) {
            $errors[] = 'Silakan pilih rating kepuasan';
        }
        
        // If no errors, save to database
        if (empty($errors)) {
            try {
                $kepuasan = new Kepuasan();
                $kepuasan->create($email, $rating, $komentar ?: null);
                $success = true;
                
            } catch (Exception $e) {
                $errors[] = 'Terjadi kesalahan. Silakan coba lagi.';
                error_log("[PELITA] Kepuasan Error: " . $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kepuasan Pelayanan - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= asset_url('css/tailwind.css') ?>">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bps-blue': '#003D7A',
                        'bps-blue-dark': '#002855',
                        'pelita-yellow': '#F47920',
                        'se2026-coral': '#E85D4C',
                        'se2026-orange': '#F47920',
                        'se2026-teal': '#00A19B'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="<?= asset_url('css/pelita.css') ?>">
</head>
<body class="bg-gray-100 min-h-screen">
    
    <!-- Header -->
    <header class="bg-pelita-yellow py-4 shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <a href="<?= base_url() ?>" class="flex items-center gap-3">
                    <span class="text-2xl">🔥</span>
                    <div>
                        <h1 class="text-lg font-bold text-bps-blue"><?= APP_NAME ?></h1>
                        <p class="text-xs text-bps-blue/70"><?= INSTITUTION_NAME ?></p>
                    </div>
                </a>
                <a href="<?= base_url() ?>" class="text-bps-blue/80 hover:text-bps-blue text-sm">
                    ← Kembali
                </a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="max-w-xl mx-auto">
            
            <!-- Title -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-pelita-yellow rounded-full mb-4">
                    <span class="text-3xl">⭐</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Survey Kepuasan</h1>
                <p class="text-gray-600">Berikan penilaian terhadap layanan kami</p>
            </div>

            <?php if ($success): ?>
            <!-- Success Message -->
            <div class="bg-white rounded-2xl shadow-lg p-8 text-center animate-fade-in">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-4xl">🙏</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Terima Kasih!</h2>
                <p class="text-gray-600 mb-6">
                    Feedback Anda sangat berarti bagi peningkatan<br>kualitas layanan kami
                </p>
                
                <a href="<?= base_url() ?>" 
                   class="inline-block bg-bps-blue text-white px-8 py-3 rounded-lg hover:bg-bps-blue/90 transition">
                    ← Kembali ke Beranda
                </a>
            </div>
            
            <?php else: ?>
            <!-- Form -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                
                <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-6">
                    <p class="font-semibold mb-2">❌ Terjadi kesalahan:</p>
                    <ul class="list-disc list-inside text-sm">
                        <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <?= csrf_field() ?>
                    
                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" required
                               value="<?= $_POST['email'] ?? '' ?>"
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-pelita-yellow focus:border-transparent"
                               placeholder="email@example.com">
                    </div>

                    <!-- Rating -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-4">
                            Bagaimana kepuasan Anda terhadap layanan kami? <span class="text-red-500">*</span>
                        </label>
                        
                        <div class="grid grid-cols-3 gap-4">
                            <!-- Sangat Puas -->
                            <label class="rating-option">
                                <input type="radio" name="rating" value="Sangat Puas" 
                                       class="hidden peer" required
                                       <?= ($_POST['rating'] ?? '') === 'Sangat Puas' ? 'checked' : '' ?>>
                                <div class="peer-checked:bg-green-100 peer-checked:border-green-500 peer-checked:ring-2 peer-checked:ring-green-200 
                                            border-2 border-gray-200 rounded-xl p-4 text-center cursor-pointer 
                                            hover:bg-gray-50 transition">
                                    <div class="text-4xl mb-2">😃</div>
                                    <div class="text-sm font-medium text-gray-700">Sangat Puas</div>
                                </div>
                            </label>
                            
                            <!-- Puas -->
                            <label class="rating-option">
                                <input type="radio" name="rating" value="Puas" 
                                       class="hidden peer"
                                       <?= ($_POST['rating'] ?? '') === 'Puas' ? 'checked' : '' ?>>
                                <div class="peer-checked:bg-yellow-100 peer-checked:border-yellow-500 peer-checked:ring-2 peer-checked:ring-yellow-200 
                                            border-2 border-gray-200 rounded-xl p-4 text-center cursor-pointer 
                                            hover:bg-gray-50 transition">
                                    <div class="text-4xl mb-2">🙂</div>
                                    <div class="text-sm font-medium text-gray-700">Puas</div>
                                </div>
                            </label>
                            
                            <!-- Kurang Puas -->
                            <label class="rating-option">
                                <input type="radio" name="rating" value="Kurang Puas" 
                                       class="hidden peer"
                                       <?= ($_POST['rating'] ?? '') === 'Kurang Puas' ? 'checked' : '' ?>>
                                <div class="peer-checked:bg-red-100 peer-checked:border-red-500 peer-checked:ring-2 peer-checked:ring-red-200 
                                            border-2 border-gray-200 rounded-xl p-4 text-center cursor-pointer 
                                            hover:bg-gray-50 transition">
                                    <div class="text-4xl mb-2">😞</div>
                                    <div class="text-sm font-medium text-gray-700">Kurang Puas</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Komentar -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Komentar / Saran (Opsional)
                        </label>
                        <textarea name="komentar" rows="3"
                                  class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-pelita-yellow"
                                  placeholder="Berikan komentar atau saran untuk peningkatan layanan kami"><?= $_POST['komentar'] ?? '' ?></textarea>
                    </div>

                    <!-- Submit -->
                    <button type="submit" 
                            class="w-full bg-pelita-yellow text-bps-blue py-4 rounded-lg font-semibold text-lg hover:bg-pelita-yellow/80 transition transform hover:-translate-y-1 hover:shadow-lg">
                        ⭐ KIRIM PENILAIAN
                    </button>
                </form>
            </div>
            <?php endif; ?>
            
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-200 mt-12">
        <div class="container mx-auto px-4 py-4 text-center text-gray-600 text-sm">
            <p>&copy; <?= APP_YEAR ?> <?= INSTITUTION_NAME ?> | <?= APP_NAME ?> v<?= APP_VERSION ?></p>
        </div>
    </footer>

    <script src="<?= asset_url('js/pelita.js') ?>"></script>
</body>
</html>
