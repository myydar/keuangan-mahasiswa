<?php

require_once __DIR__ . '/../../config/database.php';

class Transaksi {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        return $this->db->insert('transaksi', [
            'user_id' => $data['user_id'],
            'kategori_id' => $data['kategori_id'],
            'judul' => $data['judul'],
            'jumlah' => $data['jumlah'],
            'mata_uang' => $data['mata_uang'] ?? 'IDR',
            'jumlah_idr' => $data['jumlah_idr'],
            'kurs_rate' => $data['kurs_rate'] ?? 1,
            'tipe' => $data['tipe'],
            'tanggal' => $data['tanggal'],
            'deskripsi' => $data['deskripsi'] ?? null
        ]);
    }
    
    public function findById($id, $userId) {
        return $this->db->fetchOne(
            "SELECT t.*, k.nama as kategori_nama, k.icon, k.color 
             FROM transaksi t 
             LEFT JOIN kategori k ON t.kategori_id = k.id 
             WHERE t.id = :id AND t.user_id = :user_id",
            ['id' => $id, 'user_id' => $userId]
        );
    }
    
    public function getByUser($userId, $limit = 50, $offset = 0, $filters = []) {
        $sql = "SELECT t.*, k.nama as kategori_nama, k.icon, k.color 
                FROM transaksi t 
                LEFT JOIN kategori k ON t.kategori_id = k.id 
                WHERE t.user_id = :user_id";
        
        $params = ['user_id' => $userId];
        
        if (!empty($filters['tipe'])) {
            $sql .= " AND t.tipe = :tipe";
            $params['tipe'] = $filters['tipe'];
        }
        
        if (!empty($filters['kategori_id'])) {
            $sql .= " AND t.kategori_id = :kategori_id";
            $params['kategori_id'] = $filters['kategori_id'];
        }
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND t.tanggal >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND t.tanggal <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        
        $sql .= " ORDER BY t.tanggal DESC, t.created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function update($id, $userId, $data) {
        $updateData = [];
        
        if (isset($data['kategori_id'])) $updateData['kategori_id'] = $data['kategori_id'];
        if (isset($data['judul'])) $updateData['judul'] = $data['judul'];
        if (isset($data['jumlah'])) $updateData['jumlah'] = $data['jumlah'];
        if (isset($data['mata_uang'])) $updateData['mata_uang'] = $data['mata_uang'];
        if (isset($data['jumlah_idr'])) $updateData['jumlah_idr'] = $data['jumlah_idr'];
        if (isset($data['kurs_rate'])) $updateData['kurs_rate'] = $data['kurs_rate'];
        if (isset($data['tipe'])) $updateData['tipe'] = $data['tipe'];
        if (isset($data['tanggal'])) $updateData['tanggal'] = $data['tanggal'];
        if (isset($data['deskripsi'])) $updateData['deskripsi'] = $data['deskripsi'];
        
        if (empty($updateData)) {
            return false;
        }
        
        $this->db->update('transaksi', $updateData, 'id = :id AND user_id = :user_id', [
            'id' => $id,
            'user_id' => $userId
        ]);
        
        return true;
    }
    
    public function delete($id, $userId) {
        $this->db->delete('transaksi', 'id = :id AND user_id = :user_id', [
            'id' => $id,
            'user_id' => $userId
        ]);
        return true;
    }
    
    public function getMonthlyStats($userId, $year, $month) {
        $sql = "SELECT 
                    tipe,
                    SUM(jumlah_idr) as total,
                    COUNT(*) as count
                FROM transaksi
                WHERE user_id = :user_id 
                AND YEAR(tanggal) = :year 
                AND MONTH(tanggal) = :month
                GROUP BY tipe";
        
        return $this->db->fetchAll($sql, [
            'user_id' => $userId,
            'year' => $year,
            'month' => $month
        ]);
    }
    
    public function getByKategori($userId, $startDate, $endDate) {
        $sql = "SELECT 
                    k.nama as kategori,
                    k.color,
                    t.tipe,
                    SUM(t.jumlah_idr) as total,
                    COUNT(t.id) as count
                FROM transaksi t
                LEFT JOIN kategori k ON t.kategori_id = k.id
                WHERE t.user_id = :user_id 
                AND t.tanggal BETWEEN :start_date AND :end_date
                GROUP BY t.kategori_id, k.nama, k.color, t.tipe
                ORDER BY total DESC";
        
        return $this->db->fetchAll($sql, [
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
    
    public function getTimeSeriesData($userId, $months = 6) {
        $sql = "SELECT 
                    DATE_FORMAT(tanggal, '%Y-%m') as month,
                    tipe,
                    SUM(jumlah_idr) as total
                FROM transaksi
                WHERE user_id = :user_id 
                AND tanggal >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                GROUP BY DATE_FORMAT(tanggal, '%Y-%m'), tipe
                ORDER BY month ASC";
        
        return $this->db->fetchAll($sql, [
            'user_id' => $userId,
            'months' => $months
        ]);
    }
    
    public function getTotalCount($userId, $filters = []) {
        $sql = "SELECT COUNT(*) as total FROM transaksi WHERE user_id = :user_id";
        $params = ['user_id' => $userId];
        
        if (!empty($filters['tipe'])) {
            $sql .= " AND tipe = :tipe";
            $params['tipe'] = $filters['tipe'];
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
}