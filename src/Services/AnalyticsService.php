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
        
        // Calculate trends
        $pemasukanTrend = $this->calculateTrend(
            $currentStats['pemasukan'] ?? 0, 
            $lastMonthStats['pemasukan'] ?? 0
        );
        
        $pengeluaranTrend = $this->calculateTrend(
            $currentStats['pengeluaran'] ?? 0, 
            $lastMonthStats['pengeluaran'] ?? 0
        );
        
        $saldo = ($currentStats['pemasukan'] ?? 0) - ($currentStats['pengeluaran'] ?? 0);
        
        return [
            'current_month' => [
                'pemasukan' => $currentStats['pemasukan'] ?? 0,
                'pengeluaran' => $currentStats['pengeluaran'] ?? 0,
                'saldo' => $saldo,
                'transaksi_count' => $currentStats['total_transaksi'] ?? 0
            ],
            'trends' => [
                'pemasukan' => $pemasukanTrend,
                'pengeluaran' => $pengeluaranTrend
            ],
            'total_balance' => $this->getTotalBalance($userId)
        ];
    }
    
    private function getMonthlyStats($userId, $month) {
        $sql = "SELECT 
                    SUM(CASE WHEN tipe = 'pemasukan' THEN jumlah_idr ELSE 0 END) as pemasukan,
                    SUM(CASE WHEN tipe = 'pengeluaran' THEN jumlah_idr ELSE 0 END) as pengeluaran,
                    COUNT(*) as total_transaksi
                FROM transaksi
                WHERE user_id = :user_id 
                AND DATE_FORMAT(tanggal, '%Y-%m') = :month";
        
        $result = $this->db->fetchOne($sql, [
            'user_id' => $userId,
            'month' => $month
        ]);
        
        return [
            'pemasukan' => (float)($result['pemasukan'] ?? 0),
            'pengeluaran' => (float)($result['pengeluaran'] ?? 0),
            'total_transaksi' => (int)($result['total_transaksi'] ?? 0)
        ];
    }
    
    private function getTotalBalance($userId) {
        $sql = "SELECT 
                    SUM(CASE WHEN tipe = 'pemasukan' THEN jumlah_idr ELSE 0 END) -
                    SUM(CASE WHEN tipe = 'pengeluaran' THEN jumlah_idr ELSE 0 END) as balance
                FROM transaksi
                WHERE user_id = :user_id";
        
        $result = $this->db->fetchOne($sql, ['user_id' => $userId]);
        return (float)($result['balance'] ?? 0);
    }
    
    private function calculateTrend($current, $previous) {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 2);
    }
    
    public function getBorosKategori($userId, $months = 3) {
        $sql = "SELECT 
                    k.nama as kategori,
                    k.color,
                    SUM(t.jumlah_idr) as total,
                    COUNT(t.id) as transaksi_count,
                    AVG(t.jumlah_idr) as rata_rata
                FROM transaksi t
                LEFT JOIN kategori k ON t.kategori_id = k.id
                WHERE t.user_id = :user_id 
                AND t.tipe = 'pengeluaran'
                AND t.tanggal >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                GROUP BY t.kategori_id
                ORDER BY total DESC
                LIMIT 5";
        
        return $this->db->fetchAll($sql, [
            'user_id' => $userId,
            'months' => $months
        ]);
    }
    
    public function getRecommendations($userId) {
        $recommendations = [];
        $currentMonth = date('Y-m');
        $lastMonth = date('Y-m', strtotime('-1 month'));
        
        // Analyze spending patterns
        $currentStats = $this->getMonthlyStats($userId, $currentMonth);
        $lastMonthStats = $this->getMonthlyStats($userId, $lastMonth);
        
        // Check if spending increased
        if ($currentStats['pengeluaran'] > $lastMonthStats['pengeluaran'] * 1.2) {
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
        } elseif ($balance < 500000) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Saldo Rendah',
                'message' => 'Saldo tersisa dibawah Rp 500.000. Pertimbangkan untuk lebih hemat.',
                'icon' => 'alert-circle'
            ];
        }
        
        // Savings suggestion
        $savingsRate = $currentStats['pemasukan'] > 0 
            ? (($currentStats['pemasukan'] - $currentStats['pengeluaran']) / $currentStats['pemasukan']) * 100 
            : 0;
        
        if ($savingsRate < 20 && $currentStats['pemasukan'] > 0) {
            $recommendations[] = [
                'type' => 'info',
                'title' => 'Tips Menabung',
                'message' => 'Usahakan menyisihkan minimal 20% dari pemasukan untuk tabungan. Saat ini tingkat tabungan: ' . round($savingsRate, 1) . '%',
                'icon' => 'piggy-bank'
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