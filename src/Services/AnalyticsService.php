<?php

require_once __DIR__ . '/../../config/database.php';

class AnalyticsService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getUserSummary($userId) {
        $currentMonth = date('Y-m');
        $lastMonth = date('Y-m', strtotime('-1 month'));
        
        // Current month stats
        $currentStats = $this->getMonthlyStats($userId, $currentMonth);
        $lastMonthStats = $this->getMonthlyStats($userId, $lastMonth);
        
        // Calculate trends - handle zero/null cases
        $pemasukanTrend = $this->calculateTrend(
            $currentStats['pemasukan'], 
            $lastMonthStats['pemasukan']
        );
        
        $pengeluaranTrend = $this->calculateTrend(
            $currentStats['pengeluaran'], 
            $lastMonthStats['pengeluaran']
        );
        
        $saldo = $currentStats['pemasukan'] - $currentStats['pengeluaran'];
        
        return [
            'current_month' => [
                'pemasukan' => $currentStats['pemasukan'],
                'pengeluaran' => $currentStats['pengeluaran'],
                'saldo' => $saldo,
                'transaksi_count' => $currentStats['total_transaksi']
            ],
            'trends' => [
                'pemasukan' => $pemasukanTrend,
                'pengeluaran' => $pengeluaranTrend
            ],
            'total_balance' => $this->getTotalBalance($userId),
            'last_month' => [
                'pemasukan' => $lastMonthStats['pemasukan'],
                'pengeluaran' => $lastMonthStats['pengeluaran']
            ]
        ];
    }
    
    private function getMonthlyStats($userId, $month) {
        $sql = "SELECT 
                    COALESCE(SUM(CASE WHEN tipe = 'pemasukan' THEN jumlah_idr ELSE 0 END), 0) as pemasukan,
                    COALESCE(SUM(CASE WHEN tipe = 'pengeluaran' THEN jumlah_idr ELSE 0 END), 0) as pengeluaran,
                    COUNT(*) as total_transaksi
                FROM transaksi
                WHERE user_id = :user_id 
                AND DATE_FORMAT(tanggal, '%Y-%m') = :month";
        
        $result = $this->db->fetchOne($sql, [
            'user_id' => $userId,
            'month' => $month
        ]);
        
        // Ensure we always return numeric values, even if no data exists
        return [
            'pemasukan' => $result ? (float)$result['pemasukan'] : 0,
            'pengeluaran' => $result ? (float)$result['pengeluaran'] : 0,
            'total_transaksi' => $result ? (int)$result['total_transaksi'] : 0
        ];
    }
    
    private function getTotalBalance($userId) {
        $sql = "SELECT 
                    COALESCE(SUM(CASE WHEN tipe = 'pemasukan' THEN jumlah_idr ELSE 0 END), 0) -
                    COALESCE(SUM(CASE WHEN tipe = 'pengeluaran' THEN jumlah_idr ELSE 0 END), 0) as balance
                FROM transaksi
                WHERE user_id = :user_id";
        
        $result = $this->db->fetchOne($sql, ['user_id' => $userId]);
        return $result ? (float)$result['balance'] : 0;
    }
    
    private function calculateTrend($current, $previous) {
        // Handle null or zero values
        $current = (float)$current;
        $previous = (float)$previous;
        
        // If both are zero, no change
        if ($current == 0 && $previous == 0) {
            return 0;
        }
        
        // If previous is zero but current has value, return 100% increase
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        // Calculate percentage change
        $trend = (($current - $previous) / $previous) * 100;
        return round($trend, 2);
    }
    
    public function getBorosKategori($userId, $months = 3) {
        $sql = "SELECT 
                    k.nama as kategori,
                    k.color,
                    COALESCE(SUM(t.jumlah_idr), 0) as total,
                    COUNT(t.id) as transaksi_count,
                    COALESCE(AVG(t.jumlah_idr), 0) as rata_rata
                FROM transaksi t
                LEFT JOIN kategori k ON t.kategori_id = k.id
                WHERE t.user_id = :user_id 
                AND t.tipe = 'pengeluaran'
                AND t.tanggal >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                GROUP BY t.kategori_id, k.nama, k.color
                HAVING total > 0
                ORDER BY total DESC
                LIMIT 5";
        
        $result = $this->db->fetchAll($sql, [
            'user_id' => $userId,
            'months' => $months
        ]);
        
        return $result ? $result : [];
    }
    
    public function getRecommendations($userId) {
        $recommendations = [];
        $currentMonth = date('Y-m');
        $lastMonth = date('Y-m', strtotime('-1 month'));
        
        // Analyze spending patterns
        $currentStats = $this->getMonthlyStats($userId, $currentMonth);
        $lastMonthStats = $this->getMonthlyStats($userId, $lastMonth);
        
        // Check if spending increased (only if there's data to compare)
        if ($lastMonthStats['pengeluaran'] > 0 && $currentStats['pengeluaran'] > $lastMonthStats['pengeluaran'] * 1.2) {
            $increase = round((($currentStats['pengeluaran'] - $lastMonthStats['pengeluaran']) / $lastMonthStats['pengeluaran']) * 100, 1);
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Pengeluaran Meningkat',
                'message' => "Pengeluaran bulan ini naik {$increase}% dari bulan lalu. Coba review kategori pengeluaran tertinggi.",
                'icon' => 'trending-up'
            ];
        }
        
        // Get most expensive categories
        $borosKategori = $this->getBorosKategori($userId, 1);
        if (!empty($borosKategori)) {
            $topCategory = $borosKategori[0];
            $recommendations[] = [
                'type' => 'info',
                'title' => 'Kategori Tertinggi',
                'message' => "Kategori '{$topCategory['kategori']}' menghabiskan Rp " . number_format($topCategory['total'], 0, ',', '.') . " bulan ini.",
                'icon' => 'chart-pie'
            ];
        }
        
        // Check balance
        $balance = $this->getTotalBalance($userId);
        if ($balance < 0) {
            $recommendations[] = [
                'type' => 'danger',
                'title' => 'Saldo Negatif',
                'message' => 'Pengeluaran melebihi pemasukan. Pertimbangkan untuk mengurangi pengeluaran atau mencari sumber pemasukan tambahan.',
                'icon' => 'alert-triangle'
            ];
        } elseif ($balance > 0 && $balance < 500000) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Saldo Rendah',
                'message' => 'Saldo tersisa dibawah Rp 500.000. Pertimbangkan untuk lebih hemat.',
                'icon' => 'alert-circle'
            ];
        }
        
        // Savings suggestion (only if there's income)
        if ($currentStats['pemasukan'] > 0) {
            $savingsRate = (($currentStats['pemasukan'] - $currentStats['pengeluaran']) / $currentStats['pemasukan']) * 100;
            
            if ($savingsRate < 20) {
                $recommendations[] = [
                    'type' => 'info',
                    'title' => 'Tips Menabung',
                    'message' => 'Usahakan menyisihkan minimal 20% dari pemasukan untuk tabungan. Saat ini tingkat tabungan: ' . round($savingsRate, 1) . '%',
                    'icon' => 'piggy-bank'
                ];
            } elseif ($savingsRate > 50) {
                $recommendations[] = [
                    'type' => 'success',
                    'title' => 'Hebat! Tabungan Bagus',
                    'message' => 'Anda berhasil menabung ' . round($savingsRate, 1) . '% dari pemasukan. Pertahankan!',
                    'icon' => 'thumbs-up'
                ];
            }
        } else if ($currentStats['transaksi_count'] == 0) {
            // No transactions yet
            $recommendations[] = [
                'type' => 'info',
                'title' => 'Mulai Catat Transaksi',
                'message' => 'Belum ada transaksi bulan ini. Mulai catat pemasukan dan pengeluaran Anda untuk analisis yang lebih baik.',
                'icon' => 'plus-circle'
            ];
        }
        
        return $recommendations;
    }
    
    public function generateCSVReport($userId, $startDate, $endDate) {
        $sql = "SELECT 
                    t.tanggal,
                    t.judul,
                    t.tipe,
                    k.nama as kategori,
                    t.jumlah,
                    t.mata_uang,
                    t.jumlah_idr,
                    t.deskripsi
                FROM transaksi t
                LEFT JOIN kategori k ON t.kategori_id = k.id
                WHERE t.user_id = :user_id 
                AND t.tanggal BETWEEN :start_date AND :end_date
                ORDER BY t.tanggal DESC";
        
        $data = $this->db->fetchAll($sql, [
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        
        // Generate CSV
        $csv = "Tanggal,Judul,Tipe,Kategori,Jumlah,Mata Uang,Jumlah IDR,Deskripsi\n";
        
        foreach ($data as $row) {
            $csv .= implode(',', [
                $row['tanggal'],
                '"' . str_replace('"', '""', $row['judul']) . '"',
                $row['tipe'],
                '"' . str_replace('"', '""', $row['kategori']) . '"',
                $row['jumlah'],
                $row['mata_uang'],
                $row['jumlah_idr'],
                '"' . str_replace('"', '""', $row['deskripsi'] ?? '') . '"'
            ]) . "\n";
        }
        
        return $csv;
    }
}