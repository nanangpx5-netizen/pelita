<?php
/**
 * Admin Header Template
 * Theme: Modern Glassmorphism (SE2026)
 */
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin' ?> - <?= APP_NAME ?></title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        'bps-blue': '#003D7A',
                        'bps-dark': '#002855',
                        'se-coral': '#E85D4C',
                        'se-orange': '#F47920',
                        'se-teal': '#00A19B',
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'float-delayed': 'float 8s ease-in-out 2s infinite',
                        'float-slow': 'float 10s ease-in-out 1s infinite',
                        'fade-in': 'fadeIn 0.8s ease-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                            '50%': { transform: 'translateY(-20px) rotate(5deg)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(30px) scale(0.95)' },
                            '100%': { opacity: '1', transform: 'translateY(0) scale(1)' },
                        },
                    },
                },
            },
        }
    </script>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .glass-sidebar {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.5);
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.4);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .nav-item.active {
            background: linear-gradient(135deg, #003D7A 0%, #002855 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 61, 122, 0.3);
        }

        .floating-blob {
            position: absolute;
            filter: blur(80px);
            opacity: 0.4;
            z-index: -1;
            pointer-events: none;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen relative overflow-x-hidden text-slate-800">

    <!-- Background Atmosphere -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="floating-blob w-[500px] h-[500px] bg-se-orange/20 -top-40 -left-20 rounded-full animate-float">
        </div>
        <div
            class="floating-blob w-[450px] h-[450px] bg-se-coral/20 top-1/2 -right-40 rounded-full animate-float-delayed">
        </div>
        <div
            class="floating-blob w-[400px] h-[400px] bg-se-teal/20 -bottom-20 left-1/4 rounded-full animate-float-slow">
        </div>
    </div>

    <div class="flex h-screen overflow-hidden relative z-10">

        <!-- Sidebar -->
        <aside class="w-72 glass-sidebar hidden lg:flex flex-col h-full absolute lg:relative z-30">
            <!-- Brand -->
            <div class="h-20 flex items-center px-8 border-b border-gray-100">
                <a href="<?= base_url('admin/') ?>" class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-bps-blue flex items-center justify-center text-white shadow-md">
                        <i class="fa-solid fa-chart-simple"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-bps-blue tracking-tight">PELITA</h1>
                        <p class="text-[10px] text-slate-500 font-semibold uppercase tracking-widest">Admin Panel</p>
                    </div>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-2">

                <p class="px-4 text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 mt-2">Menu Utama</p>

                <a href="<?= base_url('admin/') ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-300 nav-item <?= basename($_SERVER['PHP_SELF']) === 'index.php' && !strpos($_SERVER['REQUEST_URI'], 'buku-tamu') && !strpos($_SERVER['REQUEST_URI'], 'kepuasan') ? 'active' : 'text-slate-600 hover:bg-white/50 hover:text-bps-blue' ?>">
                    <i class="fa-solid fa-chart-pie w-5"></i>
                    Dashboard
                </a>

                <a href="<?= base_url('admin/buku-tamu/') ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-300 nav-item <?= strpos($_SERVER['REQUEST_URI'], 'buku-tamu') ? 'active' : 'text-slate-600 hover:bg-white/50 hover:text-bps-blue' ?>">
                    <i class="fa-solid fa-book-open w-5"></i>
                    Buku Tamu
                </a>

                <a href="<?= base_url('admin/kepuasan/') ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-300 nav-item <?= strpos($_SERVER['REQUEST_URI'], 'kepuasan') ? 'active' : 'text-slate-600 hover:bg-white/50 hover:text-bps-blue' ?>">
                    <i class="fa-solid fa-star w-5"></i>
                    Survei Kepuasan
                </a>

                <a href="<?= base_url('admin/antrian/') ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-300 nav-item <?= strpos($_SERVER['REQUEST_URI'], 'antrian') ? 'active' : 'text-slate-600 hover:bg-white/50 hover:text-bps-blue' ?>">
                    <i class="fa-solid fa-ticket w-5"></i>
                    Antrian
                </a>

                <div class="my-6 border-t border-gray-100 dark:border-gray-700"></div>

                <p class="px-4 text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Lainnya</p>

                <a href="https://halopst.web.bps.go.id/?mfd=3500" target="_blank"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-slate-600 hover:bg-white/50 hover:text-bps-blue transition-all duration-300">
                    <i class="fa-solid fa-headset w-5"></i>
                    Halo PST
                    <i class="fa-solid fa-up-right-from-square text-[10px] ml-auto opacity-50"></i>
                </a>

                <a href="https://skd.bps.go.id/skd/p/3509" target="_blank"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-slate-600 hover:bg-white/50 hover:text-bps-blue transition-all duration-300">
                    <i class="fa-solid fa-clipboard-question w-5"></i>
                    Survei Kebutuhan Data
                    <i class="fa-solid fa-up-right-from-square text-[10px] ml-auto opacity-50"></i>
                </a>

                <div class="my-6 border-t border-gray-100 dark:border-gray-700"></div>

                <p class="px-4 text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Sistem</p>

                <a href="<?= base_url('admin/sync/') ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-300 nav-item <?= strpos($_SERVER['REQUEST_URI'], 'sync') ? 'active' : 'text-slate-600 hover:bg-white/50 hover:text-bps-blue' ?>">
                    <i class="fa-solid fa-rotate w-5"></i>
                    Sync Monitor
                </a>

                <a href="<?= base_url() ?>" target="_blank"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-slate-600 hover:bg-white/50 hover:text-bps-blue transition-all duration-300">
                    <i class="fa-solid fa-external-link-alt w-5"></i>
                    Lihat Website
                </a>

                <a href="<?= base_url('admin/logout.php') ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-red-500 hover:bg-red-50 hover:text-red-700 transition-all duration-300">
                    <i class="fa-solid fa-right-from-bracket w-5"></i>
                    Keluar
                </a>

            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-gray-100 text-center">
                <p class="text-xs font-semibold text-slate-600">&copy; 2026 BPS Kabupaten Jember</p>
                <p class="text-xs font-bold text-bps-blue mt-1">PELITA v1.0.0</p>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden relative">

            <!-- Top Header -->
            <header class="glass-header h-20 flex items-center justify-between px-6 lg:px-8 z-20">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()"
                        class="lg:hidden w-10 h-10 rounded-xl bg-white/50 hover:bg-white flex items-center justify-center text-slate-600 shadow-sm transition-all">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h2 class="text-xl font-bold text-slate-800 tracking-tight"><?= $pageTitle ?? 'Dashboard' ?></h2>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex flex-col items-end mr-2">
                        <span class="text-sm font-bold text-slate-700"><?= admin_name() ?></span>
                        <span class="text-xs text-slate-500">Administrator</span>
                    </div>
                    <div
                        class="w-10 h-10 rounded-full bg-gradient-to-br from-bps-blue to-bps-dark text-white flex items-center justify-center font-bold shadow-md border-2 border-white">
                        <?= strtoupper(substr(admin_name(), 0, 1)) ?>
                    </div>
                </div>
            </header>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-6 lg:p-8 scroll-smooth">
                <!-- Flash Messages -->
                <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 flex items-center gap-3 text-green-700" id="flashSuccess">
                    <i class="fa-solid fa-circle-check"></i>
                    <span class="text-sm font-medium"><?= $_SESSION['flash_success'] ?></span>
                    <button onclick="this.parentElement.remove()" class="ml-auto text-green-500 hover:text-green-700"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <?php unset($_SESSION['flash_success']); endif; ?>

                <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 flex items-center gap-3 text-red-700" id="flashError">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span class="text-sm font-medium"><?= $_SESSION['flash_error'] ?></span>
                    <button onclick="this.parentElement.remove()" class="ml-auto text-red-500 hover:text-red-700"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <?php unset($_SESSION['flash_error']); endif; ?>
                <!-- Content injected here -->