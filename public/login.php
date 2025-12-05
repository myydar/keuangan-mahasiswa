<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/User.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi';
    } else {
        $userModel = new User();
        $user = $userModel->findByEmail($email);
        
        if ($user && $userModel->verifyPassword($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Email atau password salah';
        }
    }
}

// Check for registration success message
if (isset($_GET['registered'])) {
    $success = 'Registrasi berhasil! Silakan login.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Keuangan Mahasiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-500 to-blue-700 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <div class="inline-block bg-white rounded-full p-4 mb-4">
                <i class="fas fa-wallet text-blue-600 text-5xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-white mb-2">Keuangan Mahasiswa</h1>
            <p class="text-blue-100">Kelola keuangan dengan mudah dan cerdas</p>
        </div>
        
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Login</h2>
            
            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                <div class="flex">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <p class="text-sm text-green-700"><?= htmlspecialchars($success) ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-semibold mb-2">
                        <i class="fas fa-envelope mr-1"></i> Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="email@example.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    >
                </div>
                
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 text-sm font-semibold mb-2">
                        <i class="fas fa-lock mr-1"></i> Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="••••••••"
                    >
                </div>
                
                <button 
                    type="submit"
                    class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-bold py-3 rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-200 shadow-lg"
                >
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-600 text-sm">
                    Belum punya akun?
                    <a href="register.php" class="text-blue-600 font-semibold hover:text-blue-700">
                        Daftar Sekarang
                    </a>
                </p>
            </div>
            
            <!-- Demo Accounts Info -->
            <div class="mt-8 bg-blue-50 rounded-lg p-4">
                <p class="text-xs text-gray-600 font-semibold mb-2">
                    <i class="fas fa-info-circle text-blue-500 mr-1"></i> Akun Demo:
                </p>
                <div class="text-xs text-gray-600 space-y-1">
                    <p><strong>Admin:</strong> admin@keuangan.test / password123</p>
                    <p><strong>User:</strong> john@student.test / password123</p>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-6">
            <p class="text-white text-sm">
                &copy; 2024 Keuangan Mahasiswa. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>