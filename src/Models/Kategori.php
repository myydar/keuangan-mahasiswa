<?php

require_once __DIR__ . '/../../config/database.php';

class Kategori {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        return $this->db->insert('kategori', [
            'user_id' => $data['user_id'],
            'nama' => $data['nama'],
            'tipe' => $data['tipe'],
            'icon' => $data['icon'] ?? 'wallet',
            'color' => $data['color'] ?? '#3b82f6'
        ]);
    }
    
    public function findById($id, $userId) {
        return $this->db->fetchOne(
            "SELECT * FROM kategori WHERE id = :id AND user_id = :user_id",
            ['id' => $id, 'user_id' => $userId]
        );
    }
    
    public function getByUser($userId, $tipe = null) {
        $sql = "SELECT * FROM kategori WHERE user_id = :user_id";
        $params = ['user_id' => $userId];
        
        if ($tipe) {
            $sql .= " AND tipe = :tipe";
            $params['tipe'] = $tipe;
        }
        
        $sql .= " ORDER BY nama ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function update($id, $userId, $data) {
        $updateData = [];
        
        if (isset($data['nama'])) $updateData['nama'] = $data['nama'];
        if (isset($data['tipe'])) $updateData['tipe'] = $data['tipe'];
        if (isset($data['icon'])) $updateData['icon'] = $data['icon'];
        if (isset($data['color'])) $updateData['color'] = $data['color'];
        
        if (empty($updateData)) {
            return false;
        }
        
        $this->db->update('kategori', $updateData, 'id = :id AND user_id = :user_id', [
            'id' => $id,
            'user_id' => $userId
        ]);
        
        return true;
    }
    
    public function delete($id, $userId) {
        $this->db->delete('kategori', 'id = :id AND user_id = :user_id', [
            'id' => $id,
            'user_id' => $userId
        ]);
        return true;
    }
    
    public function getUsageCount($id, $userId) {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM transaksi 
             WHERE kategori_id = :id AND user_id = :user_id",
            ['id' => $id, 'user_id' => $userId]
        );
        
        return $result['count'] ?? 0;
    }
}