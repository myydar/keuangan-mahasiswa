<?php
session_start();
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/JatuhTempo.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userModel = new User();
$user = $userModel->findById($userId);
$jatuhTempoModel = new JatuhTempo();

$message = '';
$error = '';

// Update overdue status
$jatuhTempoModel->updateOverdueStatus();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            try {
                $jatuhTempoModel->create([
                    'user_id' => $userId,
                    'judul' => $_POST['judul'],
                    'jumlah' => $_POST['jumlah'],
                    'tanggal_jatuh_tempo' => $_POST['tanggal_jatuh_tempo'],
                    'kategori' => $_POST['kategori']
                ]);
                $message = 'Jatuh tempo berhasil ditambahkan!';
            } catch (Exception $e) {
                $error = 'Gagal menambahkan: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'update_status' && isset($_POST['id'])) {
            try {
                $jatuhTempoModel->update($_POST['id'], $userId, [
                    'status' => $_POST['status']
                ]);
                $message = 'Status berhasil diupdate!';
            } catch (Exception $e) {
                $error = 'Gagal mengupdate: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
            try {
                $jatuhTempoModel->delete($_POST['id'], $userId);
                $message = 'Berhasil dihapus!';
            } catch (Exception $e) {
                $error = 'Gagal menghapus: ' . $e->getMessage();
            }
        }
    }
}

$pending = $jatuhTempoModel->getByUser($userId, 'pending');
$paid = $jatuhTempoModel->getByUser($userId, 'paid');
$overdue = $jatuhTempoModel->getByUser($userId, 'overdue');
$upcoming = $jatuhTempoModel->getUpcoming($userId, 7);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jatuh Tempo - Keuangan Mahasiswa</title>
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
                
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Jatuh Tempo Pembayaran</h1>
                        <p class="text-gray-600">Kelola jadwal pembayaran SPP, kos, dan tagihan lainnya</p>
                    </div>
                    <button onclick="openModal()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i> Tambah Jadwal
                    </button>
                </div>
                
                <!-- Upcoming Alerts -->
                <?php if (!empty($upcoming)): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
                    <h3 class="font-semibold text-yellow-800 mb-2">
                        <i class="fas fa-bell mr-2"></i>Pembayaran Segera Jatuh Tempo (7 Hari Ke Depan)
                    </h3>
                    <ul class="space-y-1">
                        <?php foreach ($upcoming as $item): ?>
                        <li class="text-sm text-yellow-700">
                            • <?= htmlspecialchars($item['judul']) ?> - 
                            <strong><?= date('d M Y', strtotime($item['tanggal_jatuh_tempo'])) ?></strong>
                            (Rp <?= number_format($item['jumlah'], 0, ',', '.') ?>)
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Pending</p>
                                <h3 class="text-2xl font-bold text-yellow-600"><?= count($pending) ?></h3>
                            </div>
                            <i class="fas fa-clock text-yellow-500 text-3xl"></i>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Overdue</p>
                                <h3 class="text-2xl font-bold text-red-600"><?= count($overdue) ?></h3>
                            </div>
                            <i class="fas fa-exclamation-triangle text-red-500 text-3xl"></i>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Paid</p>
                                <h3 class="text-2xl font-bold text-green-600"><?= count($paid) ?></h3>
                            </div>
                            <i class="fas fa-check-circle text-green-500 text-3xl"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Tabs -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="border-b">
                        <nav class="flex">
                            <button onclick="showTab('pending')" class="tab-btn px-6 py-4 font-medium border-b-2 border-blue-500 text-blue-600" data-tab="pending">
                                Pending (<?= count($pending) ?>)
                            </button>
                            <button onclick="showTab('overdue')" class="tab-btn px-6 py-4 font-medium text-gray-600 hover:text-gray-800" data-tab="overdue">
                                Overdue (<?= count($overdue) ?>)
                            </button>
                            <button onclick="showTab('paid')" class="tab-btn px-6 py-4 font-medium text-gray-600 hover:text-gray-800" data-tab="paid">
                                Paid (<?= count($paid) ?>)
                            </button>
                        </nav>
                    </div>
                    
                    <!-- Pending Tab -->
                    <div id="tab-pending" class="tab-content p-6">
                        <?php if (empty($pending)): ?>
                        <p class="text-center text-gray-500 py-8">Tidak ada pembayaran pending</p>
                        <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($pending as $item): ?>
                            <div class="border rounded-lg p-4 hover:bg-gray-50">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($item['judul']) ?></h4>
                                        <p class="text-sm text-gray-600 mt-1">
                                            <i class="fas fa-tag mr-1"></i><?= ucfirst($item['kategori']) ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-calendar mr-1"></i><?= date('d M Y', strtotime($item['tanggal_jatuh_tempo'])) ?>
                                        </p>
                                        <p class="text-lg font-bold text-blue-600 mt-2">
                                            Rp <?= number_format($item['jumlah'], 0, ',', '.') ?>
                                        </p>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <input type="hidden" name="status" value="paid">
                                            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 text-sm">
                                                <i class="fas fa-check mr-1"></i> Tandai Lunas
                                            </button>
                                        </form>
                                        <form method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 text-sm">
                                                <i class="fas fa-trash mr-1"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Overdue Tab -->
                    <div id="tab-overdue" class="tab-content p-6 hidden">
                        <?php if (empty($overdue)): ?>
                        <p class="text-center text-gray-500 py-8">Tidak ada pembayaran overdue</p>
                        <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($overdue as $item): ?>
                            <div class="border border-red-200 bg-red-50 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-red-800"><?= htmlspecialchars($item['judul']) ?></h4>
                                        <p class="text-sm text-red-600 mt-1">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Terlambat!
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-calendar mr-1"></i><?= date('d M Y', strtotime($item['tanggal_jatuh_tempo'])) ?>
                                        </p>
                                        <p class="text-lg font-bold text-red-600 mt-2">
                                            Rp <?= number_format($item['jumlah'], 0, ',', '.') ?>
                                        </p>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <form method="POST">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <input type="hidden" name="status" value="paid">
                                            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 text-sm">
                                                <i class="fas fa-check mr-1"></i> Tandai Lunas
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Paid Tab -->
                    <div id="tab-paid" class="tab-content p-6 hidden">
                        <?php if (empty($paid)): ?>
                        <p class="text-center text-gray-500 py-8">Belum ada pembayaran yang lunas</p>
                        <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($paid as $item): ?>
                            <div class="border border-green-200 bg-green-50 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-green-800"><?= htmlspecialchars($item['judul']) ?></h4>
                                        <p class="text-sm text-green-600 mt-1">
                                            <i class="fas fa-check-circle mr-1"></i>Lunas
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-calendar mr-1"></i><?= date('d M Y', strtotime($item['tanggal_jatuh_tempo'])) ?>
                                        </p>
                                        <p class="text-lg font-bold text-green-600 mt-2">
                                            Rp <?= number_format($item['jumlah'], 0, ',', '.') ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Modal -->
    <div id="modalJatuhTempo" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
            <div class="p-6 border-b">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold">Tambah Jatuh Tempo</h3>
                    <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="create">
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Judul</label>
                    <input type="text" name="judul" required class="w-full border border-gray-300 rounded-lg px-4 py-2" placeholder="e.g., Bayar SPP Semester">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori</label>
                    <select name="kategori" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        <option value="akademik">Akademik (SPP, UTS, UAS)</option>
                        <option value="tempat_tinggal">Tempat Tinggal (Kos, Kontrakan)</option>
                        <option value="utilitas">Utilitas (Listrik, Air, Internet)</option>
                        <option value="teknologi">Teknologi (Gadget, Subscription)</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah (IDR)</label>
                    <input type="number" name="jumlah" required class="w-full border border-gray-300 rounded-lg px-4 py-2" placeholder="0">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Jatuh Tempo</label>
                    <input type="date" name="tanggal_jatuh_tempo" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeModal()" class="px-6 py-2 border rounded-lg hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="assets/js/app.js"></script>
    <script>
    function openModal() {
        document.getElementById('modalJatuhTempo').classList.remove('hidden');
    }
    
    function closeModal() {
        document.getElementById('modalJatuhTempo').classList.add('hidden');
    }
    
    function showTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('border-blue-500', 'text-blue-600');
            el.classList.add('text-gray-600');
        });
        
        document.getElementById('tab-' + tabName).classList.remove('hidden');
        document.querySelector('[data-tab="' + tabName + '"]').classList.add('border-blue-500', 'text-blue-600');
    }
    </script>
</body>
</html>