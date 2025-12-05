-- Seed Data untuk Testing
USE keuangan_mahasiswa;

-- Insert Users (password: password123)
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@keuangan.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('John Mahasiswa', 'john@student.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Jane Doe', 'jane@student.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Insert Kategori untuk User ID 2 (John)
INSERT INTO kategori (user_id, nama, tipe, icon, color) VALUES
(2, 'Uang Saku', 'pemasukan', 'hand-coins', '#10b981'),
(2, 'Beasiswa', 'pemasukan', 'graduation-cap', '#3b82f6'),
(2, 'Part-time', 'pemasukan', 'briefcase', '#8b5cf6'),
(2, 'Makanan & Minuman', 'pengeluaran', 'utensils', '#ef4444'),
(2, 'Transportasi', 'pengeluaran', 'bus', '#f59e0b'),
(2, 'Kos', 'pengeluaran', 'home', '#ec4899'),
(2, 'Buku & ATK', 'pengeluaran', 'book', '#06b6d4'),
(2, 'Entertainment', 'pengeluaran', 'gamepad', '#a855f7'),
(2, 'Pakaian', 'pengeluaran', 'shirt', '#14b8a6'),
(2, 'Kesehatan', 'pengeluaran', 'heart-pulse', '#f43f5e');

-- Insert Kategori untuk User ID 3 (Jane)
INSERT INTO kategori (user_id, nama, tipe, icon, color) VALUES
(3, 'Uang Bulanan', 'pemasukan', 'wallet', '#10b981'),
(3, 'Freelance', 'pemasukan', 'laptop', '#3b82f6'),
(3, 'Makan', 'pengeluaran', 'utensils', '#ef4444'),
(3, 'Transportasi', 'pengeluaran', 'car', '#f59e0b'),
(3, 'Kos', 'pengeluaran', 'home', '#ec4899');

-- Insert Transaksi (3 bulan terakhir untuk visualisasi)
INSERT INTO transaksi (user_id, kategori_id, judul, jumlah, mata_uang, jumlah_idr, kurs_rate, tipe, tanggal, deskripsi) VALUES
-- Oktober 2024
(2, 1, 'Uang Saku Bulanan', 2000000, 'IDR', 2000000, 1, 'pemasukan', '2024-10-01', 'Kiriman dari orang tua'),
(2, 2, 'Beasiswa Semester', 5000000, 'IDR', 5000000, 1, 'pemasukan', '2024-10-05', 'Beasiswa prestasi'),
(2, 4, 'Makan Siang Kampus', 25000, 'IDR', 25000, 1, 'pengeluaran', '2024-10-02', 'Kantin kampus'),
(2, 4, 'Makan Malam', 35000, 'IDR', 35000, 1, 'pengeluaran', '2024-10-03', 'Warteg'),
(2, 5, 'Bensin Motor', 50000, 'IDR', 50000, 1, 'pengeluaran', '2024-10-04', 'Pertalite'),
(2, 6, 'Bayar Kos', 1200000, 'IDR', 1200000, 1, 'pengeluaran', '2024-10-05', 'Kos bulan Oktober'),
(2, 7, 'Buku Kuliah', 150000, 'IDR', 150000, 1, 'pengeluaran', '2024-10-07', 'Buku Algoritma'),
(2, 4, 'Kopi & Snack', 30000, 'IDR', 30000, 1, 'pengeluaran', '2024-10-08', 'Kafe dekat kampus'),
(2, 5, 'Grab ke Mall', 45000, 'IDR', 45000, 1, 'pengeluaran', '2024-10-10', 'Transportation'),
(2, 8, 'Nonton Bioskop', 50000, 'IDR', 50000, 1, 'pengeluaran', '2024-10-12', 'Film terbaru'),
-- November 2024
(2, 1, 'Uang Saku Bulanan', 2000000, 'IDR', 2000000, 1, 'pemasukan', '2024-11-01', 'Kiriman dari orang tua'),
(2, 3, 'Freelance Web', 1500000, 'IDR', 1500000, 1, 'pemasukan', '2024-11-10', 'Project website toko'),
(2, 4, 'Makan di Warteg', 180000, 'IDR', 180000, 1, 'pengeluaran', '2024-11-03', 'Makan seminggu'),
(2, 5, 'Bensin', 100000, 'IDR', 100000, 1, 'pengeluaran', '2024-11-05', 'Full tank'),
(2, 6, 'Bayar Kos', 1200000, 'IDR', 1200000, 1, 'pengeluaran', '2024-11-06', 'Kos bulan November'),
(2, 4, 'Makan Fast Food', 85000, 'IDR', 85000, 1, 'pengeluaran', '2024-11-08', 'KFC'),
(2, 8, 'Subscription Netflix', 54000, 'IDR', 54000, 1, 'pengeluaran', '2024-11-15', 'Bulanan'),
(2, 9, 'Beli Kaos', 120000, 'IDR', 120000, 1, 'pengeluaran', '2024-11-18', 'Uniqlo sale'),
(2, 4, 'Makan di Resto', 200000, 'IDR', 200000, 1, 'pengeluaran', '2024-11-20', 'Dinner dengan teman'),
(2, 5, 'Parkir & Tol', 35000, 'IDR', 35000, 1, 'pengeluaran', '2024-11-22', 'Jalan-jalan'),
-- Desember 2024
(2, 1, 'Uang Saku Bulanan', 2500000, 'IDR', 2500000, 1, 'pemasukan', '2024-12-01', 'Bonus akhir tahun'),
(2, 3, 'Part-time Tutor', 800000, 'IDR', 800000, 1, 'pemasukan', '2024-12-03', 'Mengajar les privat'),
(2, 4, 'Groceries', 250000, 'IDR', 250000, 1, 'pengeluaran', '2024-12-02', 'Belanja bulanan'),
(2, 5, 'Bensin Motor', 75000, 'IDR', 75000, 1, 'pengeluaran', '2024-12-04', 'Shell'),
(2, 6, 'Bayar Kos', 1200000, 'IDR', 1200000, 1, 'pengeluaran', '2024-12-05', 'Kos bulan Desember'),
(2, 4, 'Makan KFC', 95000, 'IDR', 95000, 1, 'pengeluaran', '2024-12-05', 'Lunch'),
-- Transaksi Jane (User 3)
(3, 11, 'Uang Bulanan', 3000000, 'IDR', 3000000, 1, 'pemasukan', '2024-11-01', 'Kiriman keluarga'),
(3, 12, 'Freelance Design', 2000000, 'IDR', 2000000, 1, 'pemasukan', '2024-11-15', 'Logo design project'),
(3, 13, 'Makan & Jajan', 500000, 'IDR', 500000, 1, 'pengeluaran', '2024-11-10', 'Sebulan'),
(3, 14, 'Transportasi', 200000, 'IDR', 200000, 1, 'pengeluaran', '2024-11-12', 'Grab & Ojol'),
(3, 15, 'Bayar Kos', 1500000, 'IDR', 1500000, 1, 'pengeluaran', '2024-11-05', 'Kos November');

-- Insert Jatuh Tempo
INSERT INTO jatuh_tempo (user_id, judul, jumlah, tanggal_jatuh_tempo, status, kategori) VALUES
(2, 'Bayar SPP Semester', 7500000, '2025-01-10', 'pending', 'akademik'),
(2, 'Bayar Kos Januari', 1200000, '2025-01-05', 'pending', 'tempat_tinggal'),
(2, 'Cicilan Laptop', 500000, '2025-01-15', 'pending', 'teknologi'),
(2, 'Tagihan Listrik Kos', 150000, '2024-12-20', 'pending', 'utilitas'),
(3, 'SPP Semester', 8000000, '2025-01-12', 'pending', 'akademik'),
(3, 'Bayar Kos', 1500000, '2025-01-03', 'pending', 'tempat_tinggal');

-- Insert sample notifications
INSERT INTO notifications (user_id, title, message, type, status, sent_at) VALUES
(2, 'Pembayaran Jatuh Tempo', 'SPP Semester jatuh tempo dalam 7 hari', 'jatuh_tempo', 'sent', NOW()),
(2, 'Pengeluaran Tinggi', 'Pengeluaran kategori Makanan & Minuman naik 25% bulan ini', 'alert', 'sent', NOW());

-- Insert Exchange Rate Cache (Sample data)
INSERT INTO exchange_rate_cache (base_currency, target_currency, rate, expires_at) VALUES
('USD', 'IDR', 15750.50, DATE_ADD(NOW(), INTERVAL 1 DAY)),
('EUR', 'IDR', 17250.75, DATE_ADD(NOW(), INTERVAL 1 DAY)),
('SGD', 'IDR', 11680.25, DATE_ADD(NOW(), INTERVAL 1 DAY)),
('MYR', 'IDR', 3520.80, DATE_ADD(NOW(), INTERVAL 1 DAY));