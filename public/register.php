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
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Semua field harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter';
    } elseif ($password !== $confirmPassword) {
        $error = 'Password dan konfirmasi password tidak cocok';
    } else {
        $userModel = new User();
        
        // Check if email already exists
        if ($userModel->findByEmail($email)) {
            $error = 'Email sudah terdaftar';
        } else {
            // Create user
            try {
                $userId = $userModel->create([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'role' => 'user'
                ]);
                
                // Create default categories for new user
                $userModel->createDefaultKategori($userId);
                
                // Redirect to login with success message
                header('Location: login.php?registered=1');
                exit;
            } catch (Exception $e) {
                $error = 'Terjadi kesalahan saat registrasi. Silakan coba lagi.';
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
    <title>Register - Keuangan Mahasiswa</title>
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
        
        <!-- Register Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Daftar Akun Baru</h2>
            
            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-semibold mb-2">
                        <i class="fas fa-user mr-1"></i> Nama Lengkap
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="John Doe"
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                    >
                </div>
                
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
                
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-semibold mb-2">
                        <i class="fas fa-lock mr-1"></i> Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        minlength="6"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Minimal 6 karakter"
                    >
                </div>
                
                <div class="mb-6">
                    <label for="confirm_password" class="block text-gray-700 text-sm font-semibold mb-2">
                        <i class="fas fa-lock mr-1"></i> Konfirmasi Password
                    </label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required
                        minlength="6"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Ketik ulang password"
                    >
                </div>
                
                <button 
                    type="submit"
                    class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-bold py-3 rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-200 shadow-lg"
                >
                    <i class="fas fa-user-plus mr-2"></i> Daftar
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-600 text-sm">
                    Sudah punya akun?
                    <a href="login.php" class="text-blue-600 font-semibold hover:text-blue-700">
                        Login Sekarang
                    </a>
                </p>
            </div>
        </div>
        
        <div class="text-center mt-6">
            <p class="text-white text-sm">
                &copy; 2024 Keuangan Mahasiswa. All rights reserved.
            </p>
        </div>
    </div>
    
    <script>
    // Password confirmation validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Password dan konfirmasi password tidak cocok!');
        }
    });
    </script>
</body>
</html>