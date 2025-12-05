<?php
session_start();
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/User.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userModel = new User();
$user = $userModel->findById($userId);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $updateData = [
            'name' => $_POST['name'],
            'email' => $_POST['email']
        ];
        
        // Update password if provided
        if (!empty($_POST['new_password'])) {
            if ($_POST['new_password'] !== $_POST['confirm_password']) {
                throw new Exception('Password konfirmasi tidak cocok');
            }
            
            // Verify current password
            $currentUser = $userModel->findById($userId);
            if (!$userModel->verifyPassword($_POST['current_password'], $currentUser['password'])) {
                throw new Exception('Password saat ini salah');
            }
            
            $updateData['password'] = $_POST['new_password'];
        }
        
        $userModel->update($userId, $updateData);
        $_SESSION['user_name'] = $updateData['name'];
        $_SESSION['user_email'] = $updateData['email'];
        
        $message = 'Profile berhasil diupdate!';
        $user = $userModel->findById($userId); // Refresh data
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Keuangan Mahasiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <?php include 'components/sidebar.php'; ?>
        
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php include 'components/navbar.php'; ?>
            
            <main class="flex-1 overflow-y-auto p-6">
                <?php if ($message): ?>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                    <p class="text-sm text-green-700"><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($message) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <p class="text-sm text-red-700"><i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?></p>
                </div>
                <?php endif; ?>
                
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-800">Profile Settings</h1>
                    <p class="text-gray-600">Kelola informasi profile Anda</p>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Profile Card -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="text-center">
                                <div class="w-32 h-32 mx-auto bg-blue-100 rounded-full flex items-center justify-center mb-4">
                                    <?php if (isset($user['avatar']) && !empty($user['avatar'])): ?>
                                        <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="w-32 h-32 rounded-full object-cover">
                                    <?php else: ?>
                                        <span class="text-5xl font-bold text-blue-600">
                                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <h2 class="text-xl font-bold text-gray-800 mb-1"><?= htmlspecialchars($user['name']) ?></h2>
                                <p class="text-sm text-gray-600 mb-1"><?= htmlspecialchars($user['email']) ?></p>
                                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                                
                                <div class="mt-6 pt-6 border-t text-left">
                                    <div class="space-y-3">
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-calendar-alt w-5 mr-3 text-gray-400"></i>
                                            <span>Bergabung: <?= date('d M Y', strtotime($user['created_at'])) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Edit Profile Form -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-6">Edit Profile</h3>
                            
                            <form method="POST" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            <i class="fas fa-user mr-1"></i> Nama Lengkap
                                        </label>
                                        <input 
                                            type="text" 
                                            name="name" 
                                            value="<?= htmlspecialchars($user['name']) ?>"
                                            required
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500"
                                        >
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            <i class="fas fa-envelope mr-1"></i> Email
                                        </label>
                                        <input 
                                            type="email" 
                                            name="email" 
                                            value="<?= htmlspecialchars($user['email']) ?>"
                                            required
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500"
                                        >
                                    </div>
                                </div>
                                
                                <div class="border-t pt-6">
                                    <h4 class="text-md font-semibold text-gray-800 mb-4">Ubah Password</h4>
                                    <p class="text-sm text-gray-600 mb-4">Kosongkan jika tidak ingin mengubah password</p>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                                <i class="fas fa-lock mr-1"></i> Password Saat Ini
                                            </label>
                                            <input 
                                                type="password" 
                                                name="current_password"
                                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500"
                                                placeholder="Masukkan password saat ini"
                                            >
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                                    <i class="fas fa-key mr-1"></i> Password Baru
                                                </label>
                                                <input 
                                                    type="password" 
                                                    name="new_password"
                                                    minlength="6"
                                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500"
                                                    placeholder="Minimal 6 karakter"
                                                >
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                                    <i class="fas fa-check-double mr-1"></i> Konfirmasi Password
                                                </label>
                                                <input 
                                                    type="password" 
                                                    name="confirm_password"
                                                    minlength="6"
                                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500"
                                                    placeholder="Ketik ulang password baru"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end gap-3 pt-6 border-t">
                                    <a href="dashboard.php" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                        Batal
                                    </a>
                                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        <i class="fas fa-save mr-2"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Danger Zone -->
                        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mt-6">
                            <h3 class="text-lg font-bold text-red-800 mb-2">
                                <i class="fas fa-exclamation-triangle mr-2"></i> Danger Zone
                            </h3>
                            <p class="text-sm text-red-700 mb-4">
                                Tindakan di bawah ini bersifat permanen dan tidak dapat dibatalkan.
                            </p>
                            <button 
                                onclick="alert('Fitur hapus akun sedang dalam pengembangan')" 
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                            >
                                <i class="fas fa-trash mr-2"></i> Hapus Akun
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="assets/js/app.js"></script>
    <script>
    // Password confirmation validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const newPassword = document.querySelector('[name="new_password"]').value;
        const confirmPassword = document.querySelector('[name="confirm_password"]').value;
        const currentPassword = document.querySelector('[name="current_password"]').value;
        
        if (newPassword && !currentPassword) {
            e.preventDefault();
            alert('Password saat ini harus diisi untuk mengubah password');
            return;
        }
        
        if (newPassword && newPassword !== confirmPassword) {
            e.preventDefault();
            alert('Password konfirmasi tidak cocok!');
        }
    });
    </script>
</body>
</html>