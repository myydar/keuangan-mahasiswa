<?php
session_start();

// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keuangan Mahasiswa - Kelola Keuangan Dengan Mudah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-white">
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-wallet text-blue-600 text-2xl mr-3"></i>
                    <span class="text-xl font-bold text-gray-800">Keuangan Mahasiswa</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="login.php" class="text-gray-700 hover:text-blue-600 font-medium">Login</a>
                    <a href="register.php" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        Daftar Gratis
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="pt-32 pb-20 bg-gradient-to-br from-blue-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-5xl font-bold text-gray-900 mb-6">
                        Kelola Keuangan Mahasiswa Lebih <span class="text-blue-600">Cerdas</span>
                    </h1>
                    <p class="text-xl text-gray-600 mb-8">
                        Aplikasi manajemen keuangan khusus mahasiswa dengan fitur kurs otomatis, 
                        notifikasi jatuh tempo, dan analitik pengeluaran yang lengkap.
                    </p>
                    <div class="flex space-x-4">
                        <a href="register.php" class="bg-blue-600 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-blue-700 transition">
                            Mulai Sekarang
                        </a>
                        <a href="login.php" class="border-2 border-blue-600 text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-blue-50 transition">
                            Login
                        </a>
                    </div>
                </div>
                <div class="hidden lg:block">
                    <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-8 shadow-2xl">
                        <div class="bg-white rounded-lg p-6 mb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-semibold text-gray-800">Total Saldo</h3>
                                <i class="fas fa-wallet text-blue-600"></i>
                            </div>
                            <p class="text-3xl font-bold text-gray-900">Rp 5.250.000</p>
                            <p class="text-sm text-green-600 mt-2">
                                <i class="fas fa-arrow-up mr-1"></i> +15.5% dari bulan lalu
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white rounded-lg p-4">
                                <p class="text-sm text-gray-600 mb-1">Pemasukan</p>
                                <p class="text-xl font-bold text-green-600">Rp 3.5jt</p>
                            </div>
                            <div class="bg-white rounded-lg p-4">
                                <p class="text-sm text-gray-600 mb-1">Pengeluaran</p>
                                <p class="text-xl font-bold text-red-600">Rp 2.8jt</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Fitur Lengkap untuk Mahasiswa</h2>
                <p class="text-xl text-gray-600">Semua yang Anda butuhkan untuk mengelola keuangan</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-gradient-to-br from-blue-50 to-white p-8 rounded-xl hover:shadow-lg transition">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-exchange-alt text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Multi-Currency</h3>
                    <p class="text-gray-600">
                        Support berbagai mata uang dengan konversi otomatis ke IDR menggunakan kurs real-time.
                    </p>
                </div>
                
                <div class="bg-gradient-to-br from-green-50 to-white p-8 rounded-xl hover:shadow-lg transition">
                    <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-chart-line text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Grafik & Analitik</h3>
                    <p class="text-gray-600">
                        Visualisasi pengeluaran dengan grafik interaktif dan rekomendasi penghematan.
                    </p>
                </div>
                
                <div class="bg-gradient-to-br from-yellow-50 to-white p-8 rounded-xl hover:shadow-lg transition">
                    <div class="bg-yellow-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-bell text-yellow-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Notifikasi Jatuh Tempo</h3>
                    <p class="text-gray-600">
                        Pengingat otomatis untuk pembayaran SPP, kos, dan tagihan lainnya.
                    </p>
                </div>
                
                <div class="bg-gradient-to-br from-purple-50 to-white p-8 rounded-xl hover:shadow-lg transition">
                    <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-tags text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Kategori Custom</h3>
                    <p class="text-gray-600">
                        Buat dan kelola kategori transaksi sesuai kebutuhan Anda.
                    </p>
                </div>
                
                <div class="bg-gradient-to-br from-red-50 to-white p-8 rounded-xl hover:shadow-lg transition">
                    <div class="bg-red-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-download text-red-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Export CSV</h3>
                    <p class="text-gray-600">
                        Download laporan keuangan dalam format CSV untuk analisis lebih lanjut.
                    </p>
                </div>
                
                <div class="bg-gradient-to-br from-indigo-50 to-white p-8 rounded-xl hover:shadow-lg transition">
                    <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-mobile-alt text-indigo-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Responsive Design</h3>
                    <p class="text-gray-600">
                        Akses dari desktop, tablet, atau smartphone dengan tampilan yang optimal.
                    </p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-blue-600 to-blue-800">
        <div class="max-w-4xl mx-auto text-center px-4">
            <h2 class="text-4xl font-bold text-white mb-6">
                Mulai Kelola Keuangan Anda Hari Ini
            </h2>
            <p class="text-xl text-blue-100 mb-8">
                Gratis! Tidak perlu kartu kredit. Daftar sekarang dan rasakan kemudahan mengelola keuangan.
            </p>
            <a href="register.php" class="inline-block bg-white text-blue-600 px-8 py-4 rounded-lg text-lg font-bold hover:bg-gray-100 transition">
                Daftar Gratis Sekarang
            </a>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-wallet text-blue-500 text-2xl mr-3"></i>
                        <span class="text-xl font-bold">Keuangan Mahasiswa</span>
                    </div>
                    <p class="text-gray-400">
                        Aplikasi manajemen keuangan terbaik untuk mahasiswa Indonesia.
                    </p>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="login.php" class="text-gray-400 hover:text-white">Login</a></li>
                        <li><a href="register.php" class="text-gray-400 hover:text-white">Register</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Contact</h4>
                    <p class="text-gray-400">
                        Email: support@keuangan.test<br>
                        GitHub: github.com/yourusername
                    </p>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2024 Keuangan Mahasiswa. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>