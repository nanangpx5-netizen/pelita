<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/BukuTamu.php';
require_once CLASSES_PATH . '/Kepuasan.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/csrf.php';

// Get statistics from database
$bukuTamu = new BukuTamu();
$kepuasan = new Kepuasan();

$bukuTamuStats = $bukuTamu->getStats();
$tamuHariIni = $bukuTamuStats['hari_ini'] ?? 0;
$kepuasanStats = $kepuasan->getStats(null, date('Y'));
$persenSangatPuas = $kepuasanStats['persen_sangat_puas'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PELITA - <?= INSTITUTION_NAME ?></title>

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
                        'pulse-slow': 'pulse 3s infinite',
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

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .glass-card:hover {
            transform: translateY(-5px) scale(1.02);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border-color: rgba(255, 255, 255, 0.8);
        }

        .gradient-text {
            background: linear-gradient(135deg, #003D7A 0%, #F47920 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .modal-backdrop {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
        }

        .floating-blob {
            position: absolute;
            filter: blur(80px);
            opacity: 0.5;
            z-index: -1;
        }

        /* Custom Scrollbar for Modal */
        .modal-scroll::-webkit-scrollbar {
            width: 8px;
        }

        .modal-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .modal-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .modal-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .rating-label input:checked+div {
            transform: scale(1.15);
            box-shadow: 0 0 0 4px rgba(0, 161, 155, 0.3);
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen relative overflow-x-hidden text-slate-800 flex flex-col">

    <!-- SPLASH SCREEN -->
    <div id="splashScreen"
        class="fixed inset-0 z-[100] flex items-center justify-center transition-opacity duration-500"
        style="background: linear-gradient(135deg, #003D7A 0%, #002855 50%, #F47920 100%);">
        <div class="text-center relative z-10 animate-fade-in">
            <!-- Logo -->
            <div
                class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center shadow-2xl backdrop-blur-sm">
                <i class="fa-solid fa-chart-simple text-white text-4xl"></i>
            </div>

            <!-- Title -->
            <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-2 tracking-tight">PELITA</h1>
            <p class="text-white/70 text-sm uppercase tracking-widest mb-8">BPS Kabupaten Jember</p>

            <!-- QR Code Container -->
            <div class="bg-white rounded-3xl p-6 inline-block shadow-2xl mb-6">
                <div id="qrcode" class="mx-auto"></div>
                <p class="text-xs text-slate-500 mt-3 font-medium">Scan untuk akses online</p>
            </div>

            <!-- Timer & Skip -->
            <div class="flex flex-col items-center gap-3">
                <p class="text-white/60 text-sm">
                    <span id="countdown">8</span> detik...
                </p>
                <button onclick="closeSplash()"
                    class="px-6 py-2 rounded-full bg-white/10 hover:bg-white/20 border border-white/30 text-white text-sm font-semibold transition-all duration-300 flex items-center gap-2">
                    <i class="fa-solid fa-forward"></i> Lewati
                </button>
            </div>
        </div>

        <!-- Decorative Blobs in Splash -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-30">
            <div class="absolute w-96 h-96 bg-se-orange/50 rounded-full -top-20 -left-20 blur-3xl animate-float"></div>
            <div
                class="absolute w-80 h-80 bg-se-coral/50 rounded-full bottom-10 -right-20 blur-3xl animate-float-delayed">
            </div>
        </div>
    </div>

    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
            (function () {
                // Check if splash was already shown this session
                if (sessionStorage.getItem('pelita_splash_shown')) {
                    document.getElementById('splashScreen').style.display = 'none';
                } else {
                    // Generate QR Code
                    new QRCode(document.getElementById("qrcode"), {
                        text: "<?= BASE_URL ?>",
                        width: 160,
                        height: 160,
                        colorDark: "#003D7A",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });

                    // Countdown Timer
                    let seconds = 8;
                    const countdownEl = document.getElementById('countdown');
                    const timer = setInterval(() => {
                        seconds--;
                        countdownEl.textContent = seconds;
                        if (seconds <= 0) {
                            clearInterval(timer);
                            closeSplash();
                        }
                    }, 1000);
                }
                // Generate QR Code for Dashboard
                document.addEventListener('DOMContentLoaded', function () {
                    new QRCode(document.getElementById("qrcode-main"), {
                        text: "<?= BASE_URL ?>",
                        width: 128,
                        height: 128,
                        colorDark: "#003D7A",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                });
            })();

        function closeSplash() {
            const splash = document.getElementById('splashScreen');
            splash.style.opacity = '0';
            setTimeout(() => {
                splash.style.display = 'none';
            }, 500);
            sessionStorage.setItem('pelita_splash_shown', 'true');
        }
    </script>

    <!-- A. Background Atmosphere -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
        <div class="floating-blob w-[500px] h-[500px] bg-se-orange/30 -top-40 -left-20 rounded-full animate-float">
        </div>
        <div
            class="floating-blob w-[450px] h-[450px] bg-se-coral/30 top-1/2 -right-40 rounded-full animate-float-delayed">
        </div>
        <div
            class="floating-blob w-[400px] h-[400px] bg-se-teal/30 -bottom-20 left-1/4 rounded-full animate-float-slow">
        </div>
    </div>

    <!-- B. Navbar -->
    <nav class="fixed top-0 inset-x-0 z-40 glass-panel shadow-sm transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-bps-blue flex items-center justify-center shadow-lg transform rotate-3 hover:rotate-0 transition-transform">
                        <i class="fa-solid fa-chart-simple text-white text-lg"></i>
                    </div>
                    <div class="flex flex-col">
                        <h1 class="text-2xl font-extrabold gradient-text leading-none tracking-tight">PELITA</h1>
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest">BPS Kabupaten
                            Jember</span>
                    </div>
                </div>

                <!-- Admin Link -->
                <a href="<?= base_url('admin/login.php') ?>"
                    class="group hidden md:flex items-center gap-2 px-5 py-2.5 rounded-full bg-slate-100 hover:bg-bps-blue hover:text-white transition-all duration-300">
                    <span class="text-sm font-semibold text-slate-600 group-hover:text-white">Admin Panel</span>
                    <i class="fa-solid fa-user-shield text-xs"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content Wrapper -->
    <main class="flex-grow pt-28 pb-12 px-4 flex flex-col justify-center">
        <div class="max-w-7xl mx-auto w-full">

            <!-- C. Hero Section -->
            <div class="text-center mb-16 max-w-4xl mx-auto animate-fade-in relative">

                <!-- Badge -->
                <div
                    class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/60 border border-se-orange/30 shadow-sm backdrop-blur-sm mb-6 animate-slide-up">
                    <div class="w-2 h-2 rounded-full bg-se-orange animate-pulse"></div>
                    <span class="text-xs font-bold text-se-coral tracking-widest">SENSUS EKONOMI 2026</span>
                </div>

                <!-- Title -->
                <h2 class="text-4xl md:text-6xl font-black text-slate-800 mb-6 leading-tigher animate-slide-up"
                    style="animation-delay: 0.1s;">
                    Menerangi <span class="text-se-orange relative">Pelayanan<svg
                            class="absolute w-full h-3 -bottom-1 left-0 text-se-orange/20" viewBox="0 0 100 10"
                            preserveAspectRatio="none">
                            <path d="M0 5 Q 50 10 100 5" stroke="currentColor" stroke-width="8" fill="none" />
                        </svg></span>,<br>
                    Memandu <span class="text-se-coral relative">Pembangunan<svg
                            class="absolute w-full h-3 -bottom-1 left-0 text-se-coral/20" viewBox="0 0 100 10"
                            preserveAspectRatio="none">
                            <path d="M0 5 Q 50 10 100 5" stroke="currentColor" stroke-width="8" fill="none" />
                        </svg></span>
                </h2>

                <!-- Description -->
                <p class="text-lg text-slate-600 mb-10 max-w-2xl mx-auto font-light animate-slide-up"
                    style="animation-delay: 0.2s;">
                    Selamat datang di Portal Layanan Statistik BPS Kabupaten Jember.
                    Dapatkan data akurat dan pelayanan prima dengan mudah dan cepat.
                </p>

                <!-- Statistics Dynamic -->
                <div class="grid grid-cols-3 gap-4 md:gap-8 max-w-2xl mx-auto animate-slide-up"
                    style="animation-delay: 0.3s;">
                    <div class="p-4 rounded-2xl bg-white/50 border border-white/60 shadow-sm">
                        <div class="text-3xl font-bold text-bps-blue mb-1"><?= $tamuHariIni ?></div>
                        <div class="text-xs text-slate-500 font-medium uppercase">Tamu Hari Ini</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-white/50 border border-white/60 shadow-sm">
                        <div class="text-3xl font-bold text-se-teal mb-1"><?= $persenSangatPuas ?>%</div>
                        <div class="text-xs text-slate-500 font-medium uppercase">Sangat Puas</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-white/50 border border-white/60 shadow-sm">
                        <div class="text-3xl font-bold text-se-orange mb-1">0</div>
                        <div class="text-xs text-slate-500 font-medium uppercase">Antrian Saat Ini</div>
                    </div>
                </div>

                <!-- QR Code Main -->
                <div class="mt-10 animate-slide-up" style="animation-delay: 0.4s;">
                    <div
                        class="bg-white/80 backdrop-blur-sm p-4 rounded-3xl inline-block shadow-lg border border-white/50 hover:scale-105 transition-transform duration-300">
                        <div id="qrcode-main"></div>
                        <p class="text-[10px] text-slate-500 mt-2 font-bold uppercase tracking-wider">Scan Akses Online
                        </p>
                    </div>
                </div>
            </div>

            <!-- D. Menu Utama (Cards) -->
            <div class="grid md:grid-cols-2 gap-6 lg:gap-10 max-w-5xl mx-auto">

                <!-- Kartu Buku Tamu -->
                <div onclick="toggleModal('modalBukuTamu')"
                    class="glass-card rounded-[2.5rem] p-8 md:p-12 cursor-pointer group relative overflow-hidden animate-slide-up text-center md:text-left flex flex-col md:flex-row items-center md:items-start gap-6 border-b-4 border-b-se-orange"
                    style="animation-delay: 0.4s;">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-se-orange/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>

                    <div
                        class="w-20 h-20 rounded-2xl bg-gradient-to-br from-se-orange to-se-coral flex shrink-0 items-center justify-center shadow-lg shadow-se-orange/30 group-hover:scale-110 transition-transform duration-500">
                        <i class="fa-solid fa-pen-to-square text-3xl text-white"></i>
                    </div>

                    <div class="flex-grow z-10">
                        <h3 class="text-2xl font-bold text-slate-800 mb-2 group-hover:text-se-orange transition-colors">
                            Isi Buku Tamu</h3>
                        <p class="text-slate-500 mb-6 text-sm leading-relaxed">
                            Formulir digital untuk pencatatan kunjungan dan permintaan data statistik.
                        </p>
                        <span
                            class="inline-flex items-center text-sm font-bold text-se-coral uppercase tracking-wide group-hover:translate-x-2 transition-transform">
                            Mulai Mengisi <i class="fa-solid fa-arrow-right ml-2"></i>
                        </span>
                    </div>
                </div>

                <!-- Kartu Survei Kepuasan -->
                <div onclick="toggleModal('modalKepuasan')"
                    class="glass-card rounded-[2.5rem] p-8 md:p-12 cursor-pointer group relative overflow-hidden animate-slide-up text-center md:text-left flex flex-col md:flex-row items-center md:items-start gap-6 border-b-4 border-b-se-teal"
                    style="animation-delay: 0.5s;">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-se-teal/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>

                    <div
                        class="w-20 h-20 rounded-2xl bg-gradient-to-br from-se-teal to-teal-600 flex shrink-0 items-center justify-center shadow-lg shadow-se-teal/30 group-hover:scale-110 transition-transform duration-500">
                        <i class="fa-solid fa-thumbs-up text-3xl text-white"></i>
                    </div>

                    <div class="flex-grow z-10">
                        <h3 class="text-2xl font-bold text-slate-800 mb-2 group-hover:text-se-teal transition-colors">
                            Survei Kepuasan</h3>
                        <p class="text-slate-500 mb-6 text-sm leading-relaxed">
                            Berikan penilaian Anda terhadap pelayanan kami untuk peningkatan kualitas.
                        </p>
                        <span
                            class="inline-flex items-center text-sm font-bold text-se-teal uppercase tracking-wide group-hover:translate-x-2 transition-transform">
                            Beri Nilai <i class="fa-solid fa-arrow-right ml-2"></i>
                        </span>
                    </div>
                </div>

                <!-- Kartu Halo PST -->
                <a href="https://halopst.web.bps.go.id/?mfd=3500" target="_blank"
                    class="glass-card rounded-[2.5rem] p-8 md:p-12 cursor-pointer group relative overflow-hidden animate-slide-up text-center md:text-left flex flex-col md:flex-row items-center md:items-start gap-6 border-b-4 border-b-purple-500 no-underline"
                    style="animation-delay: 0.6s;">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>

                    <div
                        class="w-20 h-20 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-700 flex shrink-0 items-center justify-center shadow-lg shadow-purple-500/30 group-hover:scale-110 transition-transform duration-500">
                        <i class="fa-solid fa-headset text-3xl text-white"></i>
                    </div>

                    <div class="flex-grow z-10">
                        <h3 class="text-2xl font-bold text-slate-800 mb-2 group-hover:text-purple-600 transition-colors">
                            Halo PST</h3>
                        <p class="text-slate-500 mb-6 text-sm leading-relaxed">
                            Hubungi Pelayanan Statistik Terpadu BPS Kabupaten Jember secara online.
                        </p>
                        <span
                            class="inline-flex items-center text-sm font-bold text-purple-600 uppercase tracking-wide group-hover:translate-x-2 transition-transform">
                            Buka Halo PST <i class="fa-solid fa-up-right-from-square ml-2 text-xs"></i>
                        </span>
                    </div>
                </a>

                <!-- Kartu Survei Kebutuhan Data -->
                <a href="https://skd.bps.go.id/skd/p/3509" target="_blank"
                    class="glass-card rounded-[2.5rem] p-8 md:p-12 cursor-pointer group relative overflow-hidden animate-slide-up text-center md:text-left flex flex-col md:flex-row items-center md:items-start gap-6 border-b-4 border-b-indigo-500 no-underline"
                    style="animation-delay: 0.7s;">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>

                    <div
                        class="w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex shrink-0 items-center justify-center shadow-lg shadow-indigo-500/30 group-hover:scale-110 transition-transform duration-500">
                        <i class="fa-solid fa-clipboard-question text-3xl text-white"></i>
                    </div>

                    <div class="flex-grow z-10">
                        <h3 class="text-2xl font-bold text-slate-800 mb-2 group-hover:text-indigo-600 transition-colors">
                            Survei Kebutuhan Data</h3>
                        <p class="text-slate-500 mb-6 text-sm leading-relaxed">
                            Isi survei kebutuhan data statistik untuk mendukung perencanaan pembangunan.
                        </p>
                        <span
                            class="inline-flex items-center text-sm font-bold text-indigo-600 uppercase tracking-wide group-hover:translate-x-2 transition-transform">
                            Isi Survei <i class="fa-solid fa-up-right-from-square ml-2 text-xs"></i>
                        </span>
                    </div>
                </a>

            </div>
        </div>
    </main>

    <!-- E. Footer -->
    <footer class="mt-auto bg-white/60 border-t border-white/50 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid md:grid-cols-2 gap-8 items-start">

                <!-- Kolom 1: Kontak -->
                <div class="space-y-4">
                    <h4 class="font-bold text-bps-blue text-lg tracking-wide border-l-4 border-se-orange pl-3">BPS
                        KABUPATEN JEMBER</h4>
                    <div class="space-y-2 text-sm text-slate-600">
                        <p class="flex items-start gap-3">
                            <i class="fa-solid fa-location-dot mt-1 text-se-coral"></i>
                            <span>Jl. Cendrawasih No. 20, Krajan, Patrang,<br>Kab. Jember, Jawa Timur 68121</span>
                        </p>
                        <p class="flex items-center gap-3">
                            <i class="fa-solid fa-envelope text-se-coral"></i>
                            <a href="mailto:bps3509@bps.go.id"
                                class="hover:text-se-orange transition-colors">bps3509@bps.go.id</a>
                        </p>
                        <p class="flex items-center gap-3">
                            <i class="fa-solid fa-phone text-se-coral"></i>
                            <span>(0331) 487642</span>
                        </p>
                    </div>
                </div>

                <!-- Kolom 2: Social Media -->
                <div class="md:text-right space-y-4">
                    <h4
                        class="font-bold text-bps-blue text-lg tracking-wide md:border-r-4 md:border-l-0 border-l-4 md:pr-3 md:pl-0 border-se-teal pl-3">
                        IKUTI KAMI</h4>
                    <div class="flex md:justify-end gap-3">
                        <a href="https://www.instagram.com/bpsjember/" target="_blank"
                            class="w-10 h-10 rounded-full bg-slate-100 hover:bg-pink-600 hover:text-white flex items-center justify-center text-slate-500 transition-all">
                            <i class="fa-brands fa-instagram text-lg"></i>
                        </a>
                        <a href="https://www.facebook.com/jemberkab.bps.go.id" target="_blank"
                            class="w-10 h-10 rounded-full bg-slate-100 hover:bg-blue-600 hover:text-white flex items-center justify-center text-slate-500 transition-all">
                            <i class="fa-brands fa-facebook-f text-lg"></i>
                        </a>
                        <a href="https://www.youtube.com/@BPSKabupatenJember" target="_blank"
                            class="w-10 h-10 rounded-full bg-slate-100 hover:bg-red-600 hover:text-white flex items-center justify-center text-slate-500 transition-all">
                            <i class="fa-brands fa-youtube text-lg"></i>
                        </a>
                        <a href="https://jemberkab.bps.go.id" target="_blank"
                            class="w-10 h-10 rounded-full bg-slate-100 hover:bg-bps-blue hover:text-white flex items-center justify-center text-slate-500 transition-all">
                            <i class="fa-solid fa-globe text-lg"></i>
                        </a>
                    </div>
                    <p class="text-xs text-slate-400 mt-4 font-medium">
                        &copy; 2026 <strong>PELITA v1.0.0</strong> - BPS Kabupaten Jember.<br>All rights reserved.
                    </p>
                </div>

            </div>
        </div>
    </footer>

    <!-- MODAL 1: BUKU TAMU -->
    <div id="modalBukuTamu" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="absolute inset-0 modal-backdrop transition-opacity opacity-0"
            onclick="toggleModal('modalBukuTamu')"></div>

        <!-- Modal Panel -->
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col transform scale-95 opacity-0 transition-all duration-300"
                id="panelBukuTamu">

                <!-- Header -->
                <div
                    class="flex-none px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 rounded-t-[2rem]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-se-orange/10 flex items-center justify-center">
                            <i class="fa-solid fa-pen-to-square text-se-orange"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Buku Tamu</h2>
                            <p class="text-xs text-gray-500">Silakan lengkapi data kunjungan Anda</p>
                        </div>
                    </div>
                    <button onclick="toggleModal('modalBukuTamu')"
                        class="w-8 h-8 rounded-full bg-white border border-gray-200 hover:bg-red-50 hover:text-red-500 hover:border-red-200 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Body (Scrollable) -->
                <div class="flex-grow overflow-y-auto p-8 modal-scroll">
                    <form action="<?= base_url('public/buku-tamu.php') ?>" method="POST" class="space-y-6">
                        <?= csrf_field() ?>

                        <!-- Section 1: Data Kunjungan -->
                        <div>
                            <h3
                                class="text-sm font-bold text-se-orange mb-4 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-clipboard-list"></i> Data Kunjungan
                            </h3>
                            <div class="grid md:grid-cols-2 gap-5">
                                <!-- Tanggal (Otomatis) -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal
                                        Kunjungan</label>
                                    <div class="w-full px-4 py-2.5 rounded-xl bg-gray-100 text-gray-600 text-sm">
                                        <?= date('d F Y') ?>
                                    </div>
                                    <input type="hidden" name="tanggal_kunjungan" value="<?= date('Y-m-d') ?>">
                                </div>
                                <!-- Tujuan -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Tujuan Kunjungan <span
                                            class="text-red-500">*</span></label>
                                    <select name="keperluan" required
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-se-orange focus:ring-2 focus:ring-se-orange/20 outline-none text-sm bg-white">
                                        <option value="">-- Pilih Tujuan --</option>
                                        <option value="Perpustakaan Tercetak">Perpustakaan Tercetak</option>
                                        <option value="Perpustakaan Digital">Perpustakaan Digital</option>
                                        <option value="Penjualan Publikasi">Penjualan Publikasi</option>
                                        <option value="Konsultasi Statistik">Konsultasi Statistik</option>
                                        <option value="Data Mikro">Data Mikro</option>
                                        <option value="Rekomendasi Kegiatan Statistik">Rekomendasi Kegiatan Statistik
                                        </option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <!-- Orang Ditemui -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Orang yang
                                        Ditemui</label>
                                    <input type="text" name="orang_ditemui"
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-se-orange focus:ring-2 focus:ring-se-orange/20 outline-none text-sm"
                                        placeholder="Nama pegawai (Opsional)">
                                </div>
                                <!-- Detail -->
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Detail
                                        Keperluan</label>
                                    <textarea name="rincian" rows="2"
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-se-orange focus:ring-2 focus:ring-se-orange/20 outline-none text-sm resize-none"
                                        placeholder="Jelaskan secara singkat keperluan Anda..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="border-t border-gray-200"></div>

                        <!-- Section 2: Informasi Pengunjung -->
                        <div>
                            <h3
                                class="text-sm font-bold text-bps-blue mb-4 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-user"></i> Informasi Pengunjung
                            </h3>
                            <div class="grid md:grid-cols-2 gap-5">
                                <!-- Nama -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Nama Lengkap <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="nama" required
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-se-orange focus:ring-2 focus:ring-se-orange/20 outline-none text-sm"
                                        placeholder="Masukkan nama lengkap">
                                </div>
                                <!-- Instansi -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Instansi / Perusahaan
                                        <span class="text-red-500">*</span></label>
                                    <input type="text" name="instansi" required
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-se-orange focus:ring-2 focus:ring-se-orange/20 outline-none text-sm"
                                        placeholder="Nama instansi atau 'Umum'">
                                </div>
                                <!-- No HP -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Nomor Telepon /
                                        WhatsApp <span class="text-red-500">*</span></label>
                                    <input type="tel" name="nohp" required
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-se-orange focus:ring-2 focus:ring-se-orange/20 outline-none text-sm"
                                        placeholder="08xxxxxxxxxx">
                                </div>
                                <!-- Email -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Alamat Email</label>
                                    <input type="email" name="email"
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-se-orange focus:ring-2 focus:ring-se-orange/20 outline-none text-sm"
                                        placeholder="email@contoh.com">
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="w-full py-4 rounded-xl bg-gradient-to-r from-se-orange to-se-coral text-white font-bold text-lg shadow-lg shadow-se-orange/30 hover:shadow-se-orange/50 transform hover:-translate-y-1 transition-all duration-300">
                            Dapatkan Nomor Antrian <i class="fa-solid fa-ticket ml-2"></i>
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 2: SURVEY KEPUASAN -->
    <div id="modalKepuasan" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 modal-backdrop transition-opacity opacity-0"
            onclick="toggleModal('modalKepuasan')"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg flex flex-col transform scale-95 opacity-0 transition-all duration-300"
                id="panelKepuasan">

                <!-- Content -->
                <div class="p-8 text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-se-teal/10 flex items-center justify-center mb-4">
                        <i class="fa-solid fa-star text-2xl text-se-teal"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Penilaian Pelayanan</h2>
                    <p class="text-gray-500 text-sm mb-8">Bagaimana pengalaman Anda menggunakan layanan kami hari ini?
                    </p>

                    <form action="<?= base_url('public/kepuasan.php') ?>" method="POST" class="space-y-8">
                        <?= csrf_field() ?>

                        <!-- Emoticon Selection -->
                        <div class="flex justify-center gap-6">
                            <!-- Kurang Puas -->
                            <label class="cursor-pointer group rating-label">
                                <input type="radio" name="rating" value="Kurang Puas" class="hidden" required>
                                <div
                                    class="w-20 h-20 rounded-2xl bg-red-50 border-2 border-transparent hover:border-red-400 flex flex-col items-center justify-center gap-2 transition-all duration-300">
                                    <i
                                        class="fa-regular fa-face-frown text-4xl text-red-400 group-hover:text-red-500 transition-colors"></i>
                                    <span class="text-[10px] font-bold text-red-400 uppercase">Kurang</span>
                                </div>
                            </label>
                            <!-- Puas -->
                            <label class="cursor-pointer group rating-label">
                                <input type="radio" name="rating" value="Puas" class="hidden">
                                <div
                                    class="w-20 h-20 rounded-2xl bg-blue-50 border-2 border-transparent hover:border-blue-400 flex flex-col items-center justify-center gap-2 transition-all duration-300">
                                    <i
                                        class="fa-regular fa-face-smile text-4xl text-blue-400 group-hover:text-blue-500 transition-colors"></i>
                                    <span class="text-[10px] font-bold text-blue-400 uppercase">Puas</span>
                                </div>
                            </label>
                            <!-- Sangat Puas -->
                            <label class="cursor-pointer group rating-label">
                                <input type="radio" name="rating" value="Sangat Puas" class="hidden">
                                <div
                                    class="w-20 h-20 rounded-2xl bg-green-50 border-2 border-transparent hover:border-green-400 flex flex-col items-center justify-center gap-2 transition-all duration-300">
                                    <i
                                        class="fa-regular fa-face-laugh-beam text-4xl text-green-500 group-hover:text-green-600 transition-colors"></i>
                                    <span class="text-[10px] font-bold text-green-500 uppercase">Sangat</span>
                                </div>
                            </label>
                        </div>

                        <!-- Email Input -->
                        <div class="text-left">
                            <label class="block text-xs font-semibold text-gray-500 mb-2">Email (Opsional)</label>
                            <div class="relative">
                                <i class="fa-solid fa-envelope absolute left-4 top-3.5 text-gray-400"></i>
                                <input type="email" name="email"
                                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:border-se-teal focus:ring-2 focus:ring-se-teal/20 outline-none text-sm transition-all"
                                    placeholder="Masukkan email untuk verifikasi">
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="flex gap-3">
                            <button type="button" onclick="toggleModal('modalKepuasan')"
                                class="flex-1 py-3 rounded-xl border border-gray-200 text-gray-500 font-semibold hover:bg-gray-50 transition-colors">Batal</button>
                            <button type="submit"
                                class="flex-[2] py-3 rounded-xl bg-gradient-to-r from-se-teal to-teal-600 text-white font-bold shadow-lg shadow-se-teal/30 hover:shadow-se-teal/50 transition-all">
                                Kirim Penilaian <i class="fa-solid fa-paper-plane ml-1"></i>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Script Logic -->
    <script>
        function toggleModal(modalID) {
            const modal = document.getElementById(modalID);
            const backdrop = modal.querySelector('.modal-backdrop');
            const panel = modal.querySelector('div[id^="panel"]');

            if (modal.classList.contains('hidden')) {
                // Open
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                // Trigger animation
                setTimeout(() => {
                    backdrop.classList.remove('opacity-0');
                    panel.classList.remove('scale-95', 'opacity-0');
                    panel.classList.add('scale-100', 'opacity-100');
                }, 10);
            } else {
                // Close
                backdrop.classList.add('opacity-0');
                panel.classList.remove('scale-100', 'opacity-100');
                panel.classList.add('scale-95', 'opacity-0');

                setTimeout(() => {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                }, 300);
            }
        }
    </script>
</body>

</html>