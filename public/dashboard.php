<?php
session_start();
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Services/AnalyticsService.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userModel = new User();
$user = $userModel->findById($userId);
$analyticsService = new AnalyticsService();

// Get dashboard data
$summary = $analyticsService->getUserSummary($userId);
$recommendations = $analyticsService->getRecommendations($userId);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Keuangan Mahasiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <?php include 'components/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <?php include 'components/navbar.php'; ?>
            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Welcome Section -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
                    <p class="text-gray-600">Selamat datang kembali, <?= htmlspecialchars($user['name']) ?>!</p>
                </div>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <!-- Total Balance -->
                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Total Saldo</p>
                                <h3 class="text-2xl font-bold text-gray-800">
                                    Rp <?= number_format($summary['total_balance'], 0, ',', '.') ?>
                                </h3>
                            </div>
                            <div class="bg-blue-100 rounded-full p-3">
                                <i class="fas fa-wallet text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Income -->
                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Pemasukan Bulan Ini</p>
                                <h3 class="text-2xl font-bold text-gray-800">
                                    Rp <?= number_format($summary['current_month']['pemasukan'], 0, ',', '.') ?>
                                </h3>
                                <p class="text-xs mt-1 <?= $summary['trends']['pemasukan'] >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                                    <i class="fas fa-<?= $summary['trends']['pemasukan'] >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                                    <?= abs($summary['trends']['pemasukan']) ?>% dari bulan lalu
                                </p>
                            </div>
                            <div class="bg-green-100 rounded-full p-3">
                                <i class="fas fa-arrow-trend-up text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Expense -->
                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Pengeluaran Bulan Ini</p>
                                <h3 class="text-2xl font-bold text-gray-800">
                                    Rp <?= number_format($summary['current_month']['pengeluaran'], 0, ',', '.') ?>
                                </h3>
                                <p class="text-xs mt-1 <?= $summary['trends']['pengeluaran'] >= 0 ? 'text-red-600' : 'text-green-600' ?>">
                                    <i class="fas fa-<?= $summary['trends']['pengeluaran'] >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                                    <?= abs($summary['trends']['pengeluaran']) ?>% dari bulan lalu
                                </p>
                            </div>
                            <div class="bg-red-100 rounded-full p-3">
                                <i class="fas fa-arrow-trend-down text-red-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Saldo Bulan Ini -->
                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Saldo Bulan Ini</p>
                                <h3 class="text-2xl font-bold text-gray-800">
                                    Rp <?= number_format($summary['current_month']['saldo'], 0, ',', '.') ?>
                                </h3>
                                <p class="text-xs mt-1 text-gray-500">
                                    <?= $summary['current_month']['transaksi_count'] ?> transaksi
                                </p>
                            </div>
                            <div class="bg-purple-100 rounded-full p-3">
                                <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recommendations -->
                <?php if (!empty($recommendations)): ?>
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Rekomendasi & Insight</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($recommendations as $rec): ?>
                        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-<?= $rec['type'] === 'danger' ? 'red' : ($rec['type'] === 'warning' ? 'yellow' : 'blue') ?>-500">
                            <div class="flex items-start">
                                <div class="bg-<?= $rec['type'] === 'danger' ? 'red' : ($rec['type'] === 'warning' ? 'yellow' : 'blue') ?>-100 rounded-full p-2 mr-3">
                                    <i class="fas fa-<?= $rec['icon'] ?> text-<?= $rec['type'] === 'danger' ? 'red' : ($rec['type'] === 'warning' ? 'yellow' : 'blue') ?>-600"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800 mb-1"><?= htmlspecialchars($rec['title']) ?></h4>
                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($rec['message']) ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <a href="transaksi.php?action=create&type=pemasukan" class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-6 hover:from-green-600 hover:to-green-700 transition duration-200">
                        <i class="fas fa-plus-circle text-3xl mb-3"></i>
                        <h3 class="text-lg font-semibold">Tambah Pemasukan</h3>
                        <p class="text-sm opacity-90">Catat pemasukan baru</p>
                    </a>
                    
                    <a href="transaksi.php?action=create&type=pengeluaran" class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg p-6 hover:from-red-600 hover:to-red-700 transition duration-200">
                        <i class="fas fa-minus-circle text-3xl mb-3"></i>
                        <h3 class="text-lg font-semibold">Tambah Pengeluaran</h3>
                        <p class="text-sm opacity-90">Catat pengeluaran baru</p>
                    </a>
                    
                    <a href="grafik.php" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg p-6 hover:from-blue-600 hover:to-blue-700 transition duration-200">
                        <i class="fas fa-chart-bar text-3xl mb-3"></i>
                        <h3 class="text-lg font-semibold">Lihat Grafik</h3>
                        <p class="text-sm opacity-90">Analisis keuangan detail</p>
                    </a>
                </div>
            </main>
        </div>
    </div>
    
    <script src="assets/js/app.js"></script>
</body>
</html>