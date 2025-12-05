<header class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <button id="sidebar-toggle" class="lg:hidden text-gray-600 hover:text-gray-800">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    <?php
                    $pageTitle = [
                        'dashboard.php' => 'Dashboard',
                        'transaksi.php' => 'Transaksi',
                        'kategori.php' => 'Kategori',
                        'jatuh-tempo.php' => 'Jatuh Tempo',
                        'grafik.php' => 'Grafik & Analitik',
                        'profile.php' => 'Profile',
                        'admin.php' => 'Admin Panel'
                    ];
                    echo $pageTitle[$currentPage] ?? 'Keuangan Mahasiswa';
                    ?>
                </h2>
                <p class="text-sm text-gray-500"><?= date('l, d F Y') ?></p>
            </div>
        </div>
        
        <div class="flex items-center space-x-4">
            <!-- Notification Bell -->
            <div class="relative">
                <button class="relative text-gray-600 hover:text-gray-800" id="notification-button">
                    <i class="fas fa-bell text-xl"></i>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" id="notification-count">0</span>
                </button>
                
                <!-- Notification Dropdown -->
                <div id="notification-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800">Notifikasi</h3>
                    </div>
                    <div id="notification-list" class="max-h-96 overflow-y-auto">
                        <p class="p-4 text-center text-gray-500 text-sm">Tidak ada notifikasi</p>
                    </div>
                </div>
            </div>
            
            <!-- User Menu Dropdown -->
            <div class="relative">
                <button class="flex items-center space-x-2 text-gray-700 hover:text-gray-900" id="user-menu-button">
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white">
                        <?php if (isset($user['avatar']) && !empty($user['avatar'])): ?>
                            <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="w-8 h-8 rounded-full">
                        <?php else: ?>
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <i class="fas fa-chevron-down text-sm"></i>
                </button>
                
                <!-- Dropdown Menu -->
                <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                    <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 rounded-t-lg">
                        <i class="fas fa-user w-5 inline-block"></i> Profile
                    </a>
                    <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50 rounded-b-lg">
                        <i class="fas fa-sign-out-alt w-5 inline-block"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
// User menu dropdown toggle
const userMenuButton = document.getElementById('user-menu-button');
const userDropdown = document.getElementById('user-dropdown');

if (userMenuButton && userDropdown) {
    userMenuButton.addEventListener('click', function(e) {
        e.stopPropagation();
        userDropdown.classList.toggle('hidden');
        notificationDropdown?.classList.add('hidden');
    });
}

// Notification dropdown toggle
const notificationButton = document.getElementById('notification-button');
const notificationDropdown = document.getElementById('notification-dropdown');

if (notificationButton && notificationDropdown) {
    notificationButton.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationDropdown.classList.toggle('hidden');
        userDropdown?.classList.add('hidden');
    });
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (userDropdown && !userMenuButton?.contains(e.target)) {
        userDropdown.classList.add('hidden');
    }
    if (notificationDropdown && !notificationButton?.contains(e.target)) {
        notificationDropdown.classList.add('hidden');
    }
});

// Load notifications
async function loadNotifications() {
    try {
        const response = await fetch('api/notifications.php');
        const data = await response.json();
        
        if (data.success && data.notifications) {
            const notificationList = document.getElementById('notification-list');
            const notificationCount = document.getElementById('notification-count');
            
            if (data.notifications.length === 0) {
                notificationList.innerHTML = '<p class="p-4 text-center text-gray-500 text-sm">Tidak ada notifikasi</p>';
                notificationCount.classList.add('hidden');
            } else {
                notificationCount.textContent = data.notifications.length;
                notificationCount.classList.remove('hidden');
                
                notificationList.innerHTML = data.notifications.map(notif => `
                    <div class="p-4 border-b border-gray-100 hover:bg-gray-50">
                        <h4 class="font-semibold text-sm text-gray-800">${notif.title}</h4>
                        <p class="text-sm text-gray-600 mt-1">${notif.message}</p>
                        <p class="text-xs text-gray-400 mt-2">${notif.created_at}</p>
                    </div>
                `).join('');
            }
        }
    } catch (error) {
        console.error('Failed to load notifications:', error);
    }
}

// Load notifications on page load
loadNotifications();

// Refresh notifications every 5 minutes
setInterval(loadNotifications, 300000);
</script>