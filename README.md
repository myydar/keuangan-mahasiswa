# Web Keuangan Mahasiswa dengan Kurs Otomatis

Aplikasi web untuk mengelola keuangan mahasiswa dengan fitur kurs mata uang otomatis, notifikasi jatuh tempo, analitik pengeluaran, dan grafik interaktif.

## 🎯 Fitur Utama

### 1. **Manajemen Transaksi**
- CRUD transaksi pemasukan dan pengeluaran
- Multi-currency support dengan konversi otomatis ke IDR
- Kategori transaksi yang dapat dikustomisasi
- Filter dan pencarian transaksi

### 2. **Kurs Mata Uang Otomatis**
- Integrasi dengan Exchange Rate API
- Caching kurs dengan TTL 24 jam
- Support multiple currencies (USD, EUR, SGD, MYR, JPY, CNY)
- Konversi otomatis ke IDR

### 3. **Jatuh Tempo & Notifikasi**
- Tracking pembayaran SPP, kos, dan tagihan lainnya
- Web Push Notification untuk reminder
- Email notification sebagai fallback
- Auto-update status pembayaran

### 4. **Grafik & Visualisasi**
- Time-series chart untuk trend pemasukan/pengeluaran
- Pie chart kategori pengeluaran
- Bar chart kategori pengeluaran tertinggi
- Export data ke CSV

### 5. **Analitik & Rekomendasi**
- Identifikasi kategori pengeluaran tertinggi
- Saran penghematan berdasarkan pola pengeluaran
- Perbandingan bulanan
- Savings rate calculation

## 🏗️ Arsitektur & Teknologi

### Backend
- **PHP Native** (OOP, Class-based)
- **MySQL/MariaDB** database
- **PDO** untuk database interaction
- **password_hash()** untuk keamanan password

### Frontend
- **Tailwind CSS** untuk styling
- **Chart.js** untuk grafik
- **Font Awesome** untuk icons
- **Vanilla JavaScript** untuk interactivity

### API Integration
- **Exchange Rate API** untuk kurs mata uang
- Web Push API untuk notifikasi

### Libraries (via Composer)
- PHPMailer untuk email notifications
- Web Push library untuk push notifications

## 📁 Struktur Folder

```
keuangan-mahasiswa/
├── config/
│   ├── database.php          # Database connection
│   ├── env.php               # Environment loader
│   └── vapid.php             # VAPID keys untuk web push
├── src/
│   ├── Controllers/          # Controller classes
│   ├── Models/               # Model classes (User, Transaksi, dll)
│   ├── Services/             # Service classes (API, Analytics, dll)
│   └── Middleware/           # Authentication middleware
├── public/
│   ├── index.php             # Landing page
│   ├── login.php             # Login page
│   ├── register.php          # Registration page
│   ├── dashboard.php         # Dashboard utama
│   ├── transaksi.php         # Manajemen transaksi
│   ├── kategori.php          # Manajemen kategori
│   ├── jatuh-tempo.php       # Jatuh tempo pembayaran
│   ├── grafik.php            # Grafik & analitik
│   ├── profile.php           # User profile
│   ├── assets/               # CSS, JS, images
│   ├── components/           # Reusable components (sidebar, navbar)
│   └── api/                  # API endpoints
├── database/
│   ├── keuangan_mahasiswa.sql  # Database schema
│   └── seed.sql              # Seed data
├── docs/
│   ├── ERD.png               # Entity Relationship Diagram
│   ├── Architecture.png      # System architecture
│   ├── UML_Diagrams.png      # UML diagrams
│   ├── Mockup.png            # UI Mockups
│   └── API_Endpoints.md      # API documentation
├── .env.example              # Environment template
├── .gitignore
├── composer.json             # Dependencies
└── README.md
```

## 🚀 Instalasi & Setup

### Prerequisites
- PHP 7.4 atau lebih tinggi
- MySQL/MariaDB 5.7+
- Composer
- Web server (Apache/Nginx) atau PHP built-in server

### Langkah Instalasi

1. **Clone Repository**
```bash
git clone https://github.com/yourusername/keuangan-mahasiswa.git
cd keuangan-mahasiswa
```

2. **Install Dependencies**
```bash
composer install
```

3. **Setup Database**
```bash
# Buat database
mysql -u root -p

CREATE DATABASE keuangan_mahasiswa;
exit;

# Import schema dan seed data
mysql -u root -p keuangan_mahasiswa < database/keuangan_mahasiswa.sql
mysql -u root -p keuangan_mahasiswa < database/seed.sql
```

4. **Setup Environment**
```bash
# Copy .env.example ke .env
cp .env.example .env

# Edit .env dengan konfigurasi Anda
nano .env
```

5. **Konfigurasi .env**
```env
# Database
DB_HOST=localhost
DB_NAME=keuangan_mahasiswa
DB_USER=root
DB_PASS=your_password

# API Keys
EXCHANGE_RATE_API_KEY=your_api_key_from_exchangerate-api.com

# Email (untuk notifikasi)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password

# Web Push VAPID Keys
# Generate dengan: vendor/bin/web-push generate-vapid-keys
VAPID_PUBLIC_KEY=your_public_key
VAPID_PRIVATE_KEY=your_private_key
```

6. **Generate VAPID Keys untuk Web Push**
```bash
vendor/bin/web-push generate-vapid-keys
# Copy output ke .env
```

7. **Jalankan Server**
```bash
# Menggunakan PHP built-in server
cd public
php -S localhost:8000

# Atau konfigurasi Apache/Nginx document root ke folder /public
```

8. **Akses Aplikasi**
- Buka browser: `http://localhost:8000`
- Login dengan akun demo atau registrasi akun baru

## 👥 Akun Demo

### Admin
- Email: `admin@keuangan.test`
- Password: `password123`

### User
- Email: `john@student.test`
- Password: `password123`

atau

- Email: `jane@student.test`
- Password: `password123`

## 📊 Database Schema

### Tabel Utama

#### `users`
- User authentication dan profile
- Role-based access (admin, user)

#### `transaksi`
- Record semua transaksi pemasukan/pengeluaran
- Multi-currency dengan konversi IDR
- Relationship ke kategori dan user

#### `kategori`
- Kategori transaksi per user
- Customizable icon dan color

#### `jatuh_tempo`
- Tracking pembayaran yang akan jatuh tempo
- Auto-notification system

#### `notifications`
- Log semua notifikasi yang terkirim
- Status tracking (sent/failed/pending)

#### `exchange_rate_cache`
- Cache kurs mata uang
- TTL 24 jam untuk efisiensi

## 🔌 API Endpoints

### Exchange Rate
```
GET /api/exchange-rate.php?from=USD&to=IDR
```

### Transaksi
```
GET    /api/transaksi.php          # List transaksi
POST   /api/transaksi.php          # Create transaksi
PUT    /api/transaksi.php?id=1     # Update transaksi
DELETE /api/transaksi.php?id=1     # Delete transaksi
```

### Kategori
```
GET    /api/kategori.php           # List kategori
POST   /api/kategori.php           # Create kategori
PUT    /api/kategori.php?id=1      # Update kategori
DELETE /api/kategori.php?id=1      # Delete kategori
```

### Analytics
```
GET /api/analytics.php              # Get analytics summary
GET /api/export-csv.php?start=2024-01-01&end=2024-12-31
```

### Notifications
```
GET  /api/notifications.php         # Get notifications
POST /api/subscribe-push.php        # Subscribe to push
```

## 📈 Grafik & Analitik

### 1. Time-Series Chart
Menampilkan trend pemasukan dan pengeluaran selama 3-12 bulan terakhir dengan line chart interaktif.

### 2. Kategori Pie Chart
Breakdown pengeluaran per kategori dalam bentuk donut chart dengan persentase.

### 3. Top Categories Bar Chart
Menampilkan 5 kategori pengeluaran tertinggi dalam bentuk horizontal bar chart.

### 4. Export ke CSV
Fitur export laporan lengkap dengan semua transaksi dalam periode tertentu.

## 🔔 Sistem Notifikasi

### Web Push Notification
- Notifikasi real-time di browser
- Bekerja bahkan saat tab tertutup
- Menggunakan Service Worker

### Email Notification
- Fallback jika browser tidak support push
- PHPMailer dengan SMTP

### Jenis Notifikasi
1. Reminder jatuh tempo (H-7, H-3, H-1)
2. Alert pengeluaran tinggi
3. Rekomendasi penghematan
4. Monthly summary

## 🎨 Fitur UI/UX

- **Responsive Design**: Mobile-first approach
- **Dark Mode**: Coming soon
- **Sidebar Navigation**: Easy access ke semua fitur
- **Real-time Updates**: AJAX untuk smooth experience
- **Loading States**: Skeleton screens dan spinners
- **Error Handling**: User-friendly error messages

## 🔐 Keamanan

- Password hashing dengan `password_hash()`
- Prepared statements untuk SQL injection prevention
- Session-based authentication
- CSRF protection
- Input validation & sanitization
- XSS protection

## 📱 Progressive Web App (PWA)

- Manifest.json untuk install ke home screen
- Service Worker untuk offline capability
- Push notification support

## 🧪 Testing

### Manual Testing
1. Login/Register functionality
2. CRUD operations semua entitas
3. Currency conversion accuracy
4. Notification delivery
5. Chart rendering
6. Export CSV functionality

### Test Data
Database sudah include seed data untuk testing dengan berbagai skenario transaksi.

## 🚢 Deployment

### Shared Hosting
1. Upload semua file ke public_html
2. Import database via phpMyAdmin
3. Update .env dengan kredensial server
4. Set document root ke folder /public

### VPS/Cloud
1. Setup LAMP/LEMP stack
2. Clone repository
3. Run composer install
4. Configure virtual host
5. Setup SSL certificate (Let's Encrypt)

## 📄 Lisensi

MIT License - Silakan gunakan untuk keperluan pendidikan dan komersial.

## 👨‍💻 Developer

Dikembangkan sebagai project tugas akhir dengan ketentuan:
- PHP Native (no framework)
- OOP Architecture
- REST API Integration
- Real-time Notifications
- Interactive Charts
- Analytics & Recommendations

## 🤝 Kontribusi

Pull requests welcome! Untuk perubahan besar, silakan buka issue terlebih dahulu.

## 📞 Support

Jika ada pertanyaan atau issue:
1. Buka GitHub Issues
2. Email: support@keuangan.test
3. Documentation: /docs folder

---

**Happy Coding! 💻💰**