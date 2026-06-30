<?php
/**
 * PELITA - Admin Login
 * @version 2.0.0
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Admin.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/csrf.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('admin/');
}

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $error = 'Sesi tidak valid. Silakan refresh halaman.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Username dan password wajib diisi';
        } else {
            $result = login($username, $password);
            
            if ($result['success']) {
                redirect('admin/');
            } else {
                $error = $result['message'];
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
    <title>Login Admin - <?= APP_NAME ?></title>
    
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

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body { font-family: 'Poppins', sans-serif; }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #003D7A 0%, #F47920 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .floating-blob {
            position: absolute;
            filter: blur(80px);
            opacity: 0.5;
            z-index: -1;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen relative overflow-hidden flex items-center justify-center p-4">

    <!-- Background Atmosphere -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
        <div class="floating-blob w-[500px] h-[500px] bg-se-orange/30 -top-40 -left-20 rounded-full animate-float"></div>
        <div class="floating-blob w-[450px] h-[450px] bg-se-coral/30 top-1/2 -right-40 rounded-full animate-float-delayed"></div>
        <div class="floating-blob w-[400px] h-[400px] bg-se-teal/30 -bottom-20 left-1/4 rounded-full animate-float-slow"></div>
    </div>

    <!-- Main Card -->
    <div class="glass-card rounded-[2rem] p-8 md:p-12 w-full max-w-lg relative animate-slide-up shadow-2xl">
        
        <!-- Header -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-bps-blue shadow-lg shadow-bps-blue/30 mb-6 transform rotate-3 hover:rotate-0 transition-transform duration-300">
                <i class="fa-solid fa-chart-simple text-3xl text-white"></i>
            </div>
            <h1 class="text-3xl font-extrabold gradient-text mb-2 tracking-tight">PELITA</h1>
            <p class="text-slate-500 font-medium tracking-wide text-sm uppercase">Admin Panel Login</p>
        </div>

        <!-- Alert Error -->
        <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-6 flex items-start gap-3 text-sm animate-fade-in">
            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
            <span><?= $error ?></span>
        </div>
        <?php endif; ?>

        <?php if (has_flash('error')): ?>
        <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-6 flex items-start gap-3 text-sm animate-fade-in">
            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
            <span><?= flash('error') ?></span>
        </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" action="" class="space-y-6">
            <?= csrf_field() ?>
            
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Username</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-se-orange transition-colors">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <input type="text" name="username" required
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-200 bg-white/50 focus:bg-white focus:border-se-orange focus:ring-4 focus:ring-se-orange/10 outline-none transition-all duration-300 font-medium text-slate-700"
                           placeholder="Masukkan username">
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Password</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-se-orange transition-colors">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <input type="password" name="password" id="password" required
                           class="w-full pl-11 pr-12 py-3.5 rounded-xl border border-gray-200 bg-white/50 focus:bg-white focus:border-se-orange focus:ring-4 focus:ring-se-orange/10 outline-none transition-all duration-300 font-medium text-slate-700"
                           placeholder="Masukkan password">
                    <button type="button" onclick="togglePassword()"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-slate-600 transition-colors cursor-pointer">
                        <i class="fa-solid fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" 
                    class="w-full py-4 rounded-xl bg-gradient-to-r from-bps-blue to-bps-dark text-white font-bold text-lg shadow-lg shadow-bps-blue/30 hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 flex items-center justify-center gap-2 group">
                <span>MASUK</span> 
                <i class="fa-solid fa-arrow-right-to-bracket group-hover:translate-x-1 transition-transform"></i>
            </button>
        </form>

        <!-- Footer Link -->
        <div class="text-center mt-8">
            <a href="<?= base_url() ?>" class="inline-flex items-center text-sm font-semibold text-slate-500 hover:text-bps-blue transition-colors group">
                <i class="fa-solid fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                Kembali ke Beranda
            </a>
        </div>
    </div>
    
    <!-- Footer Info -->
    <div class="fixed bottom-6 text-xs text-slate-400 font-medium text-center w-full pointer-events-none">
        &copy; <?= APP_YEAR ?> <strong><?= INSTITUTION_NAME ?></strong>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
