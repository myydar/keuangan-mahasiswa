<?php
session_start();
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/Transaksi.php';
require_once __DIR__ . '/../src/Services/AnalyticsService.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userModel = new User();
$user = $userModel->findById($userId);
$transaksiModel = new Transaksi();
$analyticsService = new AnalyticsService();

// Get data for charts
$timeSeriesData = $transaksiModel->getTimeSeriesData($userId, 6);
$kategoriData = $transaksiModel->getByKategori(
    $userId, 
    date('Y-m-01'), 
    date('Y-m-t')
);
$borosKategori = $analyticsService->getBorosKategori($userId, 3);

// Prepare data for JavaScript
$chartData = [
    'timeSeries' => $timeSeriesData,
    'kategori' => $kategoriData,
    'boros' => $borosKategori
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grafik & Analitik - Keuangan Mahasiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <?php include 'components/sidebar.php'; ?>
        
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php include 'components/navbar.php'; ?>
            
            <main class="flex-1 overflow-y-auto p-6">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-800">Grafik & Analitik</h1>
                    <p class="text-gray-600">Visualisasi data keuangan Anda</p>
                </div>
                
                <!-- Filter Period -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                    <div class="flex items-center space-x-4">
                        <label class="text-sm font-semibold text-gray-700">Periode:</label>
                        <select id="period-select" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                            <option value="3">3 Bulan Terakhir</option>
                            <option value="6" selected>6 Bulan Terakhir</option>
                            <option value="12">12 Bulan Terakhir</option>
                        </select>
                        
                        <button onclick="exportChartData()" class="ml-auto bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition">
                            <i class="fas fa-download mr-2"></i> Export CSV
                        </button>
                    </div>
                </div>
                
                <!-- Time Series Chart -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                        Trend Pemasukan & Pengeluaran
                    </h2>
                    <div class="h-80">
                        <canvas id="timeSeriesChart"></canvas>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Category Pie Chart -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">
                            <i class="fas fa-chart-pie text-purple-600 mr-2"></i>
                            Pengeluaran per Kategori
                        </h2>
                        <div class="h-80">
                            <canvas id="categoryPieChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Top Spending Categories -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">
                            <i class="fas fa-chart-bar text-red-600 mr-2"></i>
                            Kategori Pengeluaran Tertinggi
                        </h2>
                        <div class="h-80">
                            <canvas id="topCategoriesChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Spending Summary Table -->
                <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-table text-gray-600 mr-2"></i>
                        Ringkasan Detail
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaksi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($borosKategori as $item): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 rounded-full mr-2" style="background-color: <?= $item['color'] ?>"></div>
                                            <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['kategori']) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        Rp <?= number_format($item['total'], 0, ',', '.') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= $item['transaksi_count'] ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        Rp <?= number_format($item['rata_rata'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
    const chartData = <?= json_encode($chartData) ?>;
    
    // Time Series Chart
    const timeSeriesCtx = document.getElementById('timeSeriesChart').getContext('2d');
    const timeSeriesLabels = [...new Set(chartData.timeSeries.map(d => d.month))].sort();
    
    const pemasukanData = timeSeriesLabels.map(month => {
        const item = chartData.timeSeries.find(d => d.month === month && d.tipe === 'pemasukan');
        return item ? parseFloat(item.total) : 0;
    });
    
    const pengeluaranData = timeSeriesLabels.map(month => {
        const item = chartData.timeSeries.find(d => d.month === month && d.tipe === 'pengeluaran');
        return item ? parseFloat(item.total) : 0;
    });
    
    new Chart(timeSeriesCtx, {
        type: 'line',
        data: {
            labels: timeSeriesLabels.map(m => {
                const [year, month] = m.split('-');
                const date = new Date(year, month - 1);
                return date.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
            }),
            datasets: [
                {
                    label: 'Pemasukan',
                    data: pemasukanData,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Pengeluaran',
                    data: pengeluaranData,
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                        }
                    }
                }
            }
        }
    });
    
    // Category Pie Chart
    const categoryPieCtx = document.getElementById('categoryPieChart').getContext('2d');
    const pengeluaranKategori = chartData.kategori.filter(k => k.tipe === 'pengeluaran');
    
    new Chart(categoryPieCtx, {
        type: 'doughnut',
        data: {
            labels: pengeluaranKategori.map(k => k.kategori),
            datasets: [{
                data: pengeluaranKategori.map(k => parseFloat(k.total)),
                backgroundColor: pengeluaranKategori.map(k => k.color),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': Rp ' + context.parsed.toLocaleString('id-ID') + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
    
    // Top Categories Bar Chart
    const topCategoriesCtx = document.getElementById('topCategoriesChart').getContext('2d');
    
    new Chart(topCategoriesCtx, {
        type: 'bar',
        data: {
            labels: chartData.boros.map(k => k.kategori),
            datasets: [{
                label: 'Total Pengeluaran',
                data: chartData.boros.map(k => parseFloat(k.total)),
                backgroundColor: chartData.boros.map(k => k.color),
                borderColor: chartData.boros.map(k => k.color),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + context.parsed.x.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value / 1000).toFixed(0) + 'k';
                        }
                    }
                }
            }
        }
    });
    
    // Export CSV function
    function exportChartData() {
        window.location.href = 'api/export-csv.php?start_date=' + getStartDate() + '&end_date=' + new Date().toISOString().split('T')[0];
    }
    
    function getStartDate() {
        const months = parseInt(document.getElementById('period-select').value);
        const date = new Date();
        date.setMonth(date.getMonth() - months);
        return date.toISOString().split('T')[0];
    }
    
    // Period change handler
    document.getElementById('period-select').addEventListener('change', function() {
        window.location.href = 'grafik.php?months=' + this.value;
    });
    </script>
</body>
</html>