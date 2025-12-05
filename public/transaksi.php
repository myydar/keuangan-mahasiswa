<?php
session_start();
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/Transaksi.php';
require_once __DIR__ . '/../src/Models/Kategori.php';
require_once __DIR__ . '/../src/Services/ExchangeRateService.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userModel = new User();
$user = $userModel->findById($userId);
$transaksiModel = new Transaksi();
$kategoriModel = new Kategori();
$exchangeService = new ExchangeRateService();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            try {
                $kursRate = 1;
                $jumlahIdr = $_POST['jumlah'];
                
                if ($_POST['mata_uang'] !== 'IDR') {
                    $kursRate = $exchangeService->getRate($_POST['mata_uang'], 'IDR');
                    $jumlahIdr = $_POST['jumlah'] * $kursRate;
                }
                
                $transaksiModel->create([
                    'user_id' => $userId,
                    'kategori_id' => $_POST['kategori_id'],
                    'judul' => $_POST['judul'],
                    'jumlah' => $_POST['jumlah'],
                    'mata_uang' => $_POST['mata_uang'],
                    'jumlah_idr' => $jumlahIdr,
                    'kurs_rate' => $kursRate,
                    'tipe' => $_POST['tipe'],
                    'tanggal' => $_POST['tanggal'],
                    'deskripsi' => $_POST['deskripsi'] ?? ''
                ]);
                
                $message = 'Transaksi berhasil ditambahkan!';
            } catch (Exception $e) {
                $error = 'Gagal menambahkan transaksi: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
            try {
                $transaksiModel->delete($_POST['id'], $userId);
                $message = 'Transaksi berhasil dihapus!';
            } catch (Exception $e) {
                $error = 'Gagal menghapus transaksi: ' . $e->getMessage();
            }
        }
    }
}

// Get filters
$filters = [];
if (isset($_GET['tipe']) && !empty($_GET['tipe'])) {
    $filters['tipe'] = $_GET['tipe'];
}
if (isset($_GET['kategori_id']) && !empty($_GET['kategori_id'])) {
    $filters['kategori_id'] = $_GET['kategori_id'];
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get data
$transaksiList = $transaksiModel->getByUser($userId, $limit, $offset, $filters);
$totalTransaksi = $transaksiModel->getTotalCount($userId, $filters);
$totalPages = ceil($totalTransaksi / $limit);

$kategoriPemasukan = $kategoriModel->getByUser($userId, 'pemasukan');
$kategoriPengeluaran = $kategoriModel->getByUser($userId, 'pengeluaran');
$currencies = $exchangeService->getSupportedCurrencies();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - Keuangan Mahasiswa</title>
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
                        <h1 class="text-3xl font-bold text-gray-800">Transaksi</h1>
                        <p class="text-gray-600">Kelola transaksi pemasukan dan pengeluaran</p>
                    </div>
                    <button onclick="openModal()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-2"></i> Tambah Transaksi
                    </button>
                </div>
                
                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                    <form method="GET" class="flex flex-wrap gap-4">
                        <select name="tipe" class="border border-gray-300 rounded-lg px-4 py-2">
                            <option value="">Semua Tipe</option>
                            <option value="pemasukan" <?= isset($filters['tipe']) && $filters['tipe'] === 'pemasukan' ? 'selected' : '' ?>>Pemasukan</option>
                            <option value="pengeluaran" <?= isset($filters['tipe']) && $filters['tipe'] === 'pengeluaran' ? 'selected' : '' ?>>Pengeluaran</option>
                        </select>
                        
                        <select name="kategori_id" class="border border-gray-300 rounded-lg px-4 py-2">
                            <option value="">Semua Kategori</option>
                            <?php foreach (array_merge($kategoriPemasukan, $kategoriPengeluaran) as $kat): ?>
                            <option value="<?= $kat['id'] ?>" <?= isset($filters['kategori_id']) && $filters['kategori_id'] == $kat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat['nama']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <button type="submit" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700">
                            <i class="fas fa-filter mr-2"></i> Filter
                        </button>
                        
                        <?php if (!empty($filters)): ?>
                        <a href="transaksi.php" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400">
                            <i class="fas fa-times mr-2"></i> Reset
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Transaction List -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($transaksiList)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-2"></i>
                                        <p>Belum ada transaksi</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($transaksiList as $trx): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= date('d M Y', strtotime($trx['tanggal'])) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($trx['judul']) ?></div>
                                        <?php if ($trx['deskripsi']): ?>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($trx['deskripsi']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: <?= $trx['color'] ?>20; color: <?= $trx['color'] ?>">
                                            <i class="fas fa-<?= $trx['icon'] ?> mr-1"></i>
                                            <?= htmlspecialchars($trx['kategori_nama']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $trx['tipe'] === 'pemasukan' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= ucfirst($trx['tipe']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="text-sm font-semibold <?= $trx['tipe'] === 'pemasukan' ? 'text-green-600' : 'text-red-600' ?>">
                                            <?= $trx['tipe'] === 'pemasukan' ? '+' : '-' ?>
                                            <?php if ($trx['mata_uang'] !== 'IDR'): ?>
                                            <?= $trx['mata_uang'] ?> <?= number_format($trx['jumlah'], 2) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-sm text-gray-900">
                                            Rp <?= number_format($trx['jumlah_idr'], 0, ',', '.') ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        <form method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $trx['id'] ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t">
                        <div class="text-sm text-gray-700">
                            Halaman <?= $page ?> dari <?= $totalPages ?>
                        </div>
                        <div class="flex gap-2">
                            <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?><?= !empty($filters) ? '&' . http_build_query($filters) : '' ?>" class="px-3 py-1 bg-white border rounded hover:bg-gray-50">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?><?= !empty($filters) ? '&' . http_build_query($filters) : '' ?>" class="px-3 py-1 bg-white border rounded hover:bg-gray-50">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Modal Add Transaction -->
    <div id="modalTransaksi" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold">Tambah Transaksi</h3>
                    <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="create">
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tipe Transaksi</label>
                    <select name="tipe" id="tipe" required class="w-full border border-gray-300 rounded-lg px-4 py-2" onchange="updateKategoriOptions()">
                        <option value="pemasukan">Pemasukan</option>
                        <option value="pengeluaran">Pengeluaran</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori</label>
                    <select name="kategori_id" id="kategori_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        <optgroup label="Pemasukan" id="kategori-pemasukan">
                            <?php foreach ($kategoriPemasukan as $kat): ?>
                            <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Pengeluaran" id="kategori-pengeluaran" style="display: none;">
                            <?php foreach ($kategoriPengeluaran as $kat): ?>
                            <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Judul</label>
                    <input type="text" name="judul" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Mata Uang</label>
                        <select name="mata_uang" id="mata_uang" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                            <?php foreach ($currencies as $code => $name): ?>
                            <option value="<?= $code ?>" <?= $code === 'IDR' ? 'selected' : '' ?>><?= $code ?> - <?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah</label>
                        <input type="number" name="jumlah" step="0.01" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal</label>
                    <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2"></textarea>
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
        document.getElementById('modalTransaksi').classList.remove('hidden');
    }
    
    function closeModal() {
        document.getElementById('modalTransaksi').classList.add('hidden');
    }
    
    function updateKategoriOptions() {
        const tipe = document.getElementById('tipe').value;
        const pemasukanGroup = document.getElementById('kategori-pemasukan');
        const pengeluaranGroup = document.getElementById('kategori-pengeluaran');
        
        if (tipe === 'pemasukan') {
            pemasukanGroup.style.display = '';
            pengeluaranGroup.style.display = 'none';
            document.querySelector('#kategori-pemasukan option').selected = true;
        } else {
            pemasukanGroup.style.display = 'none';
            pengeluaranGroup.style.display = '';
            document.querySelector('#kategori-pengeluaran option').selected = true;
        }
    }
    </script>
</body>
</html>