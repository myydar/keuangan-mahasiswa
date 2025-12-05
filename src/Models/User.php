<?php

require_once __DIR__ . '/../../config/database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        return $this->db->insert('users', [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $hashedPassword,
            'role' => $data['role'] ?? 'user'
        ]);
    }
    
    public function findByEmail($email) {
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE email = :email",
            ['email' => $email]
        );
    }
    
    public function findById($id) {
        return $this->db->fetchOne(
            "SELECT id, name, email, role, avatar, created_at FROM users WHERE id = :id",
            ['id' => $id]
        );
    }
    
    public function update($id, $data) {
        $updateData = [];
        
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        
        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }
        
        if (isset($data['password']) && !empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (isset($data['avatar'])) {
            $updateData['avatar'] = $data['avatar'];
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        $this->db->update('users', $updateData, 'id = :id', ['id' => $id]);
        return true;
    }
    
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public function getAll($limit = 100, $offset = 0) {
        return $this->db->fetchAll(
            "SELECT id, name, email, role, created_at FROM users 
             ORDER BY created_at DESC LIMIT :limit OFFSET :offset",
            ['limit' => $limit, 'offset' => $offset]
        );
    }
    
    public function delete($id) {
        $this->db->delete('users', 'id = :id', ['id' => $id]);
        return true;
    }
    
    public function createDefaultKategori($userId) {
        $defaultKategori = [
            // Pemasukan
            ['user_id' => $userId, 'nama' => 'Uang Saku', 'tipe' => 'pemasukan', 'icon' => 'hand-coins', 'color' => '#10b981'],
            ['user_id' => $userId, 'nama' => 'Beasiswa', 'tipe' => 'pemasukan', 'icon' => 'graduation-cap', 'color' => '#3b82f6'],
            ['user_id' => $userId, 'nama' => 'Part-time', 'tipe' => 'pemasukan', 'icon' => 'briefcase', 'color' => '#8b5cf6'],
            // Pengeluaran
            ['user_id' => $userId, 'nama' => 'Makanan & Minuman', 'tipe' => 'pengeluaran', 'icon' => 'utensils', 'color' => '#ef4444'],
            ['user_id' => $userId, 'nama' => 'Transportasi', 'tipe' => 'pengeluaran', 'icon' => 'bus', 'color' => '#f59e0b'],
            ['user_id' => $userId, 'nama' => 'Kos', 'tipe' => 'pengeluaran', 'icon' => 'home', 'color' => '#ec4899'],
            ['user_id' => $userId, 'nama' => 'Buku & ATK', 'tipe' => 'pengeluaran', 'icon' => 'book', 'color' => '#06b6d4'],
            ['user_id' => $userId, 'nama' => 'Entertainment', 'tipe' => 'pengeluaran', 'icon' => 'gamepad', 'color' => '#a855f7'],
        ];
        
        foreach ($defaultKategori as $kategori) {
            $this->db->insert('kategori', $kategori);
        }
    }
}