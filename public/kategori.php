<?php
session_start();
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/Kategori.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userModel = new User();
$user = $userModel->findById($userId);
$kategoriModel = new Kategori();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            try {
                $kategoriModel->create([
                    'user_id' => $userId,
                    'nama' => $_POST['nama'],
                    'tipe' => $_POST['tipe'],
                    'icon' => $_POST['icon'] ?? 'wallet',
                    'color' => $_POST['color'] ?? '#3b82f6'
                ]);
                $message = 'Kategori berhasil ditambahkan!';
            } catch (Exception $e) {
                $error = 'Gagal menambahkan kategori: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'update' && isset($_POST['id'])) {
            try {
                $kategoriModel->update($_POST['id'], $userId, [
                    'nama' => $_POST['nama'],
                    'icon' => $_POST['icon'],
                    'color' => $_POST['color']
                ]);
                $message = 'Kategori berhasil diupdate!';
            } catch (Exception $e) {
                $error = 'Gagal mengupdate kategori: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
            try {
                $usageCount = $kategoriModel->getUsageCount($_POST['id'], $userId);
                if ($usageCount > 0) {
                    $error = "Tidak dapat menghapus kategori yang masih digunakan pada {$usageCount} transaksi!";
                } else {
                    $kategoriModel->delete($_POST['id'], $userId);
                    $message = 'Kategori berhasil dihapus!';
                }
            } catch (Exception $e) {
                $error = 'Gagal menghapus kategori: ' . $e->getMessage();
            }
        }
    }
}

$kategoriPemasukan = $kategoriModel->getByUser($userId, 'pemasukan');
$kategoriPengeluaran = $kategoriModel->getByUser($userId, 'pengeluaran');

$icons = ['wallet', 'hand-coins', 'graduation-cap', 'briefcase', 'utensils', 'bus', 'car', 'home', 'book', 'gamepad', 'shirt', 'heart-pulse', 'laptop', 'shopping-cart', 'gift', 'plane', 'film', 'music'];
$colors = ['#3b82f6', '#10b981', '#ef4444', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#14b8a6'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori - Keuangan Mahasiswa</title>
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
                    <div class="flex">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <p class="text-sm text-green-700"><?= htmlspecialchars($message) ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <div class="flex">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Kategori</h1>
                        <p class="text-gray-600">Kelola kategori transaksi Anda</p>
                    </div>
                    <button onclick="openModal()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-2"></i> Tambah Kategori
                    </button>
                </div>
                
                <!-- Kategori Pemasukan -->
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-arrow-up text-green-600 mr-2"></i>
                        Kategori Pemasukan
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($kategoriPemasukan as $kat): ?>
                        <div class="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 rounded-lg flex items-center justify-center text-xl" style="background-color: <?= $kat['color'] ?>20; color: <?= $kat['color'] ?>">
                                        <i class="fas fa-<?= $kat['icon'] ?>"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($kat['nama']) ?></h3>
                                        <p class="text-xs text-gray-500"><?= $kategoriModel->getUsageCount($kat['id'], $userId) ?> transaksi</p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="editKategori(<?= htmlspecialchars(json_encode($kat)) ?>)" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $kat['id'] ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Kategori Pengeluaran -->
                <div>
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-arrow-down text-red-600 mr-2"></i>
                        Kategori Pengeluaran
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($kategoriPengeluaran as $kat): ?>
                        <div class="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 rounded-lg flex items-center justify-center text-xl" style="background-color: <?= $kat['color'] ?>20; color: <?= $kat['color'] ?>">
                                        <i class="fas fa-<?= $kat['icon'] ?>"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($kat['nama']) ?></h3>
                                        <p class="text-xs text-gray-500"><?= $kategoriModel->getUsageCount($kat['id'], $userId) ?> transaksi</p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="editKategori(<?= htmlspecialchars(json_encode($kat)) ?>)" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $kat['id'] ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Modal -->
    <div id="modalKategori" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
            <div class="p-6 border-b">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold" id="modalTitle">Tambah Kategori</h3>
                    <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <form method="POST" id="formKategori" class="p-6 space-y-4">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="kategoriId">
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Kategori</label>
                    <input type="text" name="nama" id="kategoriNama" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
                
                <div id="tipeField">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tipe</label>
                    <select name="tipe" id="kategoriTipe" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        <option value="pemasukan">Pemasukan</option>
                        <option value="pengeluaran">Pengeluaran</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Icon</label>
                    <div class="grid grid-cols-6 gap-2">
                        <?php foreach ($icons as $icon): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="icon" value="<?= $icon ?>" class="hidden icon-radio" <?= $icon === 'wallet' ? 'checked' : '' ?>>
                            <div class="icon-option border-2 rounded-lg p-2 text-center hover:bg-gray-50 transition">
                                <i class="fas fa-<?= $icon ?> text-xl"></i>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Warna</label>
                    <div class="grid grid-cols-8 gap-2">
                        <?php foreach ($colors as $color): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="color" value="<?= $color ?>" class="hidden color-radio" <?= $color === '#3b82f6' ? 'checked' : '' ?>>
                            <div class="color-option w-10 h-10 rounded-full border-2 hover:scale-110 transition" style="background-color: <?= $color ?>"></div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeModal()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="assets/js/app.js"></script>
    <script>
    function openModal() {
        document.getElementById('modalKategori').classList.remove('hidden');
        document.getElementById('modalTitle').textContent = 'Tambah Kategori';
        document.getElementById('formAction').value = 'create';
        document.getElementById('formKategori').reset();
        document.getElementById('tipeField').style.display = 'block';
        updateIconSelection();
        updateColorSelection();
    }
    
    function closeModal() {
        document.getElementById('modalKategori').classList.add('hidden');
    }
    
    function editKategori(kategori) {
        document.getElementById('modalKategori').classList.remove('hidden');
        document.getElementById('modalTitle').textContent = 'Edit Kategori';
        document.getElementById('formAction').value = 'update';
        document.getElementById('kategoriId').value = kategori.id;
        document.getElementById('kategoriNama').value = kategori.nama;
        document.getElementById('kategoriTipe').value = kategori.tipe;
        document.getElementById('tipeField').style.display = 'none';
        
        // Set icon
        document.querySelectorAll('.icon-radio').forEach(radio => {
            radio.checked = radio.value === kategori.icon;
        });
        
        // Set color
        document.querySelectorAll('.color-radio').forEach(radio => {
            radio.checked = radio.value === kategori.color;
        });
        
        updateIconSelection();
        updateColorSelection();
    }
    
    function updateIconSelection() {
        document.querySelectorAll('.icon-option').forEach(el => {
            const radio = el.previousElementSibling;
            if (radio.checked) {
                el.classList.add('border-blue-500', 'bg-blue-50');
            } else {
                el.classList.remove('border-blue-500', 'bg-blue-50');
            }
        });
    }
    
    function updateColorSelection() {
        document.querySelectorAll('.color-option').forEach(el => {
            const radio = el.previousElementSibling;
            if (radio.checked) {
                el.classList.add('ring-4', 'ring-blue-300');
            } else {
                el.classList.remove('ring-4', 'ring-blue-300');
            }
        });
    }
    
    document.querySelectorAll('.icon-radio').forEach(radio => {
        radio.addEventListener('change', updateIconSelection);
    });
    
    document.querySelectorAll('.color-radio').forEach(radio => {
        radio.addEventListener('change', updateColorSelection);
    });
    </script>
</body>
</html>