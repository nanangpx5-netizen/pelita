<?php
/**
 * PELITA - Admin Password Reset (Web Interface)
 * @version 1.0.0
 * 
 * ⚠️  PENTING: Hapus file ini setelah selesai menggunakan untuk alasan keamanan!
 * DELETE THIS FILE AFTER USE FOR SECURITY!
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Admin.php';

$message = '';
$error = '';
$success = false;

// Handle reset form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validasi
    if (empty($username) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Semua field harus diisi!';
    } elseif (strlen($newPassword) < 5) {
        $error = 'Password minimal harus 5 karakter!';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Password tidak sama!';
    } else {
        try {
            $admin = new Admin();
            $user = $admin->findByUsername($username);
            
            if (!$user) {
                $error = "Admin dengan username '{$username}' tidak ditemukan!";
            } else {
                if ($admin->changePassword($user['id'], $newPassword)) {
                    $success = true;
                    $message = "✓ Password admin '{$username}' berhasil direset!";
                } else {
                    $error = 'Gagal mengubah password. Silakan coba lagi.';
                }
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password Admin - PELITA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 100%;
            padding: 40px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        button:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background-color: #fee;
            border-left: 4px solid #c33;
            color: #c33;
        }
        
        .alert-success {
            background-color: #efe;
            border-left: 4px solid #3c3;
            color: #3c3;
        }
        
        .warning {
            background-color: #fef3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
            font-size: 13px;
            color: #856404;
            line-height: 1.5;
        }
        
        .warning strong {
            display: block;
            margin-bottom: 8px;
        }
        
        .success-info {
            background-color: #efe;
            border: 1px solid #3c3;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
            font-size: 13px;
            color: #155724;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Reset Password Admin</h1>
            <p>PELITA - Guest Book Management System</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($message) ?>
            </div>
            <div class="success-info">
                <strong>Login Info:</strong><br>
                Username: <code><?= htmlspecialchars($username) ?></code><br>
                Password: <code><?= htmlspecialchars($newPassword) ?></code><br><br>
                <strong>Selanjutnya:</strong> Login ke <a href="admin/login.php" style="color: #155724; text-decoration: underline;">admin/login.php</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username Admin:</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Contoh: admin"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Password Baru:</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Minimal 5 karakter"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password:</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Ketik ulang password baru"
                        required
                    >
                </div>
                
                <button type="submit">Reset Password</button>
            </form>
            
            <div class="warning">
                <strong>⚠️ Peringatan Keamanan:</strong>
                Pastikan Anda menghapus file ini (<code>reset-password.php</code>) setelah selesai menggunakannya untuk mencegah akses tidak sah!
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
