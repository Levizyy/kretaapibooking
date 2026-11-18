# Sistem Pemesanan Tiket Kereta Api

Aplikasi PHP dan MySQL untuk pemesanan tiket kereta api, dengan panel admin dan panel pengguna.

## 📋 Fitur Utama

### Admin Panel
- **Dashboard Admin**
  - Tampilan statistik penjualan real-time
  - Chart dan diagram berbagai jenis (Bar, Line, Pie, Doughnut)
  - Laporan tujuan tiket paling ramai
  - Trend revenue 12 bulan terakhir
  - Status pembayaran distribution
  - Daily booking statistics

- **Manajemen Kereta**
  - Add, Edit, Delete kereta api
  - Kelola jenis kereta (Express, Lokal, Premium)
  - Status kereta (Aktif, Nonaktif, Maintenance)

- **Manajemen Rute**
  - Buat rute perjalanan kereta
  - Set harga untuk dewasa, anak, dan bayi
  - Kelola kapasitas dan jadwal operasional
  - Filter berdasarkan stasiun dan tanggal

- **Manajemen Stasiun**
  - Add, Edit, Delete stasiun
  - Simpan informasi lokasi dan kontak stasiun

- **Manajemen Pengguna**
  - Lihat daftar pengguna terdaftar
  - Aktifkan/nonaktifkan akun user
  - Hapus user jika diperlukan

- **Manajemen Booking**
  - View semua booking dari user
  - Filter berdasarkan status pembayaran
  - Lihat detail penumpang dan harga

- **Laporan & Analisis**
  - Revenue trend chart (Line Chart)
  - Top 10 tujuan paling ramai (Horizontal Bar Chart)
  - Status pembayaran distribution (Pie Chart)
  - Jenis kereta distribution (Doughnut Chart)
  - Jenis penumpang distribution (Bar Chart)
  - Daily booking analytics (Area Chart)
  - Summary statistics

### User Panel
- **Dashboard**
  - Statistik pemesanan personal
  - Daftar booking terbaru
  - Quick access ke booking baru

- **Cari & Pesan Tiket**
  - Search kereta berdasarkan:
    - Stasiun awal dan tujuan
    - Tanggal perjalanan
  - Lihat semua kereta yang tersedia
  - Harga untuk dewasa, anak, dan bayi
  - Informasi jadwal dan durasi perjalanan

- **Proses Booking**
  - Pilih jumlah penumpang (dewasa, anak, bayi)
  - Input data penumpang (nama, umur, identitas)
  - Hitung total harga otomatis
  - Konfirmasi booking

- **Tiket Saya**
  - Lihat semua booking
  - Filter: Semua, Pending, Lunas, Perjalanan Mendatang, Selesai
  - Lihat detail booking lengkap
  - Cetak tiket

- **Profile**
  - Edit data profil (nama, email, telepon)
  - Ubah password
  - Lihat statistik pemesanan

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Charts**: Chart.js 3.9.1
- **Server**: XAMPP (Apache + MySQL)

## 📦 Instalasi

### 1. Persiapan
- Install XAMPP (https://www.apachefriends.org/)
- Jalankan Apache dan MySQL dari XAMPP Control Panel

### 2. Setup Database
1. Buka phpMyAdmin di browser: `http://localhost/phpmyadmin`
2. Buat database bernama `kereta_api_db`.
3. Pilih database tersebut, lalu import file schema-only:
   - Buka tab "Import"
   - Pilih file `database/schema.sql`
   - Klik "Go" untuk import

### 3. Deploy Aplikasi
1. Copy folder `kereta_api_booking` ke dalam folder:
   - Windows: `C:\xampp\htdocs\`
   - Linux: `/opt/lampp/htdocs/`

2. Buka browser dan akses:
   - `http://localhost/kereta_api_booking/`

### 4. Login
1. Daftar akun melalui halaman registrasi.
2. Untuk membuat akun administrator pertama, pilih database `kereta_api_db` di phpMyAdmin lalu jalankan query berikut, ganti `your_username` dengan username yang baru didaftarkan:

   ```sql
   UPDATE users SET role = 'admin' WHERE username = 'your_username';
   ```

## 📁 Struktur Folder

```
kereta_api_booking/
├── admin/                  # Admin Panel
│   ├── index.php          # Dashboard Admin
│   ├── kereta.php         # Manajemen Kereta
│   ├── rute.php           # Manajemen Rute
│   ├── booking.php        # View Booking
│   ├── stasiun.php        # Manajemen Stasiun
│   ├── pengguna.php       # Manajemen Pengguna
│   ├── laporan.php        # Laporan & Analisis
│   └── includes/
│       └── sidebar.php    # Sidebar Navigation
├── user/                   # User Panel
│   ├── index.php          # User Dashboard
│   ├── cari-tiket.php     # Search & Browse
│   ├── pesan-tiket.php    # Booking Form
│   ├── tiket-saya.php     # My Tickets
│   └── profile.php        # User Profile
├── assets/
│   ├── css/
│   │   ├── admin-style.css    # Admin styling
│   │   ├── admin-booking.css  # Admin booking page
│   │   ├── user-style.css     # User styling
│   │   └── user-account.css   # Ticket and profile pages
│   └── js/
├── includes/
│   └── config.php         # Database Config
├── uploads/               # File Storage
├── database/
│   └── schema.sql         # Schema only; contains no sample user or booking data
├── kereta_api_project_new/ # Additional login and registration source files
├── index.php              # Main Index
├── login.php              # Login Page
├── register.php           # Registration Page
├── logout.php             # Logout Handler
└── README.md              # Documentation
```

## 🔍 Fitur Diagram & Laporan

Admin dapat mengakses laporan komprehensif dengan berbagai jenis diagram:

1. **Revenue Trend Chart** - Trend revenue 12 bulan terakhir (Line Chart)
2. **Top Destinations** - 10 tujuan tiket paling ramai (Bar Chart)
3. **Payment Status** - Distribusi status pembayaran (Pie Chart)
4. **Train Type Distribution** - Jenis kereta yang dipesan (Doughnut Chart)
5. **Passenger Type** - Distribusi jenis penumpang (Bar Chart)
6. **Daily Analytics** - Booking harian 30 hari terakhir (Area Chart)

## 💡 Tips Penggunaan

### Untuk Admin:
1. Konfigurasi data master (Kereta, Stasiun)
2. Buat rute perjalanan dengan jadwal dan harga
3. Monitor booking dan pembayaran
4. Lihat analisis mendalam di menu "Laporan & Analisis"

### Untuk User:
1. Daftar akun baru
2. Cari kereta berdasarkan tujuan dan tanggal
3. Pilih kereta dan lanjut ke booking
4. Isi data penumpang dengan lengkap
5. Lihat tiket di menu "Tiket Saya"

## 🔐 Keamanan

- Implementasi saat ini masih menggunakan MD5 untuk password; ini tidak aman untuk penggunaan produksi dan harus dimigrasikan ke `password_hash()` / `password_verify()`.
- Session management yang aman
- Input sanitization untuk mencegah SQL injection
- Role-based access control (Admin/User)

## 🐛 Troubleshooting

**Error: "Connection failed"**
- Pastikan MySQL sudah running
- Cek konfigurasi di `includes/config.php`

**Error: "Database tidak ditemukan"**
- Buat database `kereta_api_db`, lalu import `database/schema.sql` ke phpMyAdmin
- Atau buat database secara manual dengan nama `kereta_api_db`

**Login tidak bisa**
- Clear browser cache
- Pastikan schema sudah di-import dan akun sudah didaftarkan

## 📝 Catatan Penting

1. Repository ini tidak menyertakan dump database atau data pengguna/pemesanan.
2. Folder `uploads/` harus writable bila fitur upload digunakan.
3. Pastikan PHP version 7.4 atau lebih tinggi.

## 🎨 Customization

Untuk mengubah warna/theme:
1. Edit file `assets/css/admin-style.css` untuk admin
2. Edit file `assets/css/user-style.css` untuk user
3. Cari kode warna gradient `linear-gradient(135deg, #667eea 0%, #764ba2 100%)`

## 📞 Support

Untuk pertanyaan atau masalah, pastikan:
1. Database sudah terinstall dengan benar
2. Folder permissions sudah sesuai (755)
3. PHP extensions: mysqli, session

## 📄 License

Bebas digunakan untuk keperluan pendidikan dan komersial.

---

**Versi**: 1.0  
**Last Updated**: 2026  
**Dibuat dengan** ❤️
