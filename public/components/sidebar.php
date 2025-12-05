<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-64 bg-gradient-to-b from-blue-600 to-blue-800 text-white flex flex-col" id="sidebar">
    <!-- Logo -->
    <div class="p-6 border-b border-blue-500">
        <div class="flex items-center space-x-3">
            <div class="bg-white rounded-lg p-2">
                <i class="fas fa-wallet text-blue-600 text-2xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold">Keuangan</h1>
                <p class="text-xs text-blue-200">Mahasiswa</p>
            </div>
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto py-4">
        <ul class="space-y-1 px-3">
            <li>
                <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition duration-200 <?= $currentPage === 'dashboard.php' ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700 hover:text-white' ?>">
                    <i class="fas fa-home w-5"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
            </li>
            
            <li>
                <a href="transaksi.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition duration-200 <?= $currentPage === 'transaksi.php' ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700 hover:text-white' ?>">
                    <i class="fas fa-exchange-alt w-5"></i>
                    <span class="font-medium">Transaksi</span>
                </a>
            </li>
            
            <li>
                <a href="kategori.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition duration-200 <?= $currentPage === 'kategori.php' ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700 hover:text-white' ?>">
                    <i class="fas fa-tags w-5"></i>
                    <span class="font-medium">Kategori</span>
                </a>
            </li>
            
            <li>
                <a href="jatuh-tempo.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition duration-200 <?= $currentPage === 'jatuh-tempo.php' ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700 hover:text-white' ?>">
                    <i class="fas fa-calendar-alt w-5"></i>
                    <span class="font-medium">Jatuh Tempo</span>
                </a>
            </li>
            
            <li>
                <a href="grafik.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition duration-200 <?= $currentPage === 'grafik.php' ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700 hover:text-white' ?>">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span class="font-medium">Grafik & Analitik</span>
                </a>
            </li>
            
            <li class="pt-4 mt-4 border-t border-blue-500">
                <div class="px-4 py-2">
                    <p class="text-xs text-blue-300 uppercase font-semibold">Pengaturan</p>
                </div>
            </li>
            
            <li>
                <a href="profile.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition duration-200 <?= $currentPage === 'profile.php' ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700 hover:text-white' ?>">
                    <i class="fas fa-user w-5"></i>
                    <span class="font-medium">Profile</span>
                </a>
            </li>
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li>
                <a href="admin.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition duration-200 <?= $currentPage === 'admin.php' ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700 hover:text-white' ?>">
                    <i class="fas fa-cog w-5"></i>
                    <span class="font-medium">Admin Panel</span>
                </a>
            </li>
            <?php endif; ?>
            
            <li>
                <a href="logout.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition duration-200 text-red-200 hover:bg-red-600 hover:text-white">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span class="font-medium">Logout</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- User Info at Bottom -->
    <div class="p-4 border-t border-blue-500">
        <div class="flex items-center space-x-3">
            <div class="bg-blue-400 rounded-full w-10 h-10 flex items-center justify-center">
                <?php if (isset($user['avatar']) && !empty($user['avatar'])): ?>
                    <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="w-10 h-10 rounded-full">
                <?php else: ?>
                    <i class="fas fa-user text-white"></i>
                <?php endif; ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate"><?= htmlspecialchars($user['name']) ?></p>
                <p class="text-xs text-blue-200 truncate"><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

<!-- Mobile Menu Button -->
<button id="mobile-menu-button" class="lg:hidden fixed bottom-4 right-4 bg-blue-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg z-50">
    <i class="fas fa-bars text-xl"></i>
</button>

<script>
// Mobile menu toggle
const mobileMenuButton = document.getElementById('mobile-menu-button');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebar-overlay');

if (mobileMenuButton) {
    mobileMenuButton.addEventListener('click', function() {
        sidebar.classList.toggle('-translate-x-full');
        sidebarOverlay.classList.toggle('hidden');
    });
}

if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', function() {
        sidebar.classList.add('-translate-x-full');
        sidebarOverlay.classList.add('hidden');
    });
}
</script>