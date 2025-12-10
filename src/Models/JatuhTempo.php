<?php

require_once __DIR__ . '/../../config/database.php';

class JatuhTempo {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        return $this->db->insert('jatuh_tempo', [
            'user_id' => $data['user_id'],
            'judul' => $data['judul'],
            'jumlah' => $data['jumlah'],
            'tanggal_jatuh_tempo' => $data['tanggal_jatuh_tempo'],
            'kategori' => $data['kategori'],
            'status' => 'pending'
        ]);
    }
    
    public function findById($id, $userId) {
        return $this->db->fetchOne(
            "SELECT * FROM jatuh_tempo WHERE id = :id AND user_id = :user_id",
            ['id' => $id, 'user_id' => $userId]
        );
    }
    
    public function getByUser($userId, $status = null) {
        $sql = "SELECT * FROM jatuh_tempo WHERE user_id = :user_id";
        $params = ['user_id' => $userId];
        
        if ($status) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }
        
        $sql .= " ORDER BY tanggal_jatuh_tempo ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getUpcoming($userId, $days = 7) {
        $sql = "SELECT *, 
                DATEDIFF(tanggal_jatuh_tempo, CURDATE()) as days_remaining
                FROM jatuh_tempo 
                WHERE user_id = :user_id 
                AND status = 'pending'
                AND tanggal_jatuh_tempo >= CURDATE()
                AND tanggal_jatuh_tempo <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
                ORDER BY tanggal_jatuh_tempo ASC";
        
        return $this->db->fetchAll($sql, [
            'user_id' => $userId,
            'days' => $days
        ]);
    }
    
    public function update($id, $userId, $data) {
        $updateData = [];
        
        if (isset($data['judul'])) $updateData['judul'] = $data['judul'];
        if (isset($data['jumlah'])) $updateData['jumlah'] = $data['jumlah'];
        if (isset($data['tanggal_jatuh_tempo'])) $updateData['tanggal_jatuh_tempo'] = $data['tanggal_jatuh_tempo'];
        if (isset($data['kategori'])) $updateData['kategori'] = $data['kategori'];
        if (isset($data['status'])) $updateData['status'] = $data['status'];
        
        if (empty($updateData)) {
            return false;
        }
        
        $this->db->update('jatuh_tempo', $updateData, 'id = :id AND user_id = :user_id', [
            'id' => $id,
            'user_id' => $userId
        ]);
        
        return true;
    }
    
    public function delete($id, $userId) {
        $this->db->delete('jatuh_tempo', 'id = :id AND user_id = :user_id', [
            'id' => $id,
            'user_id' => $userId
        ]);
        return true;
    }
    
    public function updateOverdueStatus() {
        $sql = "UPDATE jatuh_tempo 
                SET status = 'overdue' 
                WHERE status = 'pending' 
                AND tanggal_jatuh_tempo < CURDATE()";
        
        $this->db->query($sql);
    }
    
    public function markNotificationSent($id) {
        $this->db->update('jatuh_tempo', 
            ['notifikasi_terkirim' => true], 
            'id = :id', 
            ['id' => $id]
        );
    }
}