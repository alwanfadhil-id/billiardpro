# Gambaran Umum Sistem BilliardPro

## 🎯 Tujuan Sistem

BilliardPro adalah sistem billing otomatis untuk usaha biliar yang dirancang untuk:
- Mengotomatiskan perhitungan durasi dan biaya penggunaan meja
- Mengelola transaksi penjualan produk tambahan (minuman/snack)
- Menyediakan laporan keuangan dan operasional harian, bulanan, dan tahunan
- Mempermudah manajemen stok produk
- Memberikan antarmuka yang intuitif bagi kasir
- Memungkinkan booking meja di waktu mendatang untuk mengurangi konflik antara walk-in dan reservasi

## 🏗️ Arsitektur Sistem

### Teknologi Inti
- **Backend**: Laravel 11 (PHP Framework)
- **Frontend**: Livewire 3 (Full-stack framework)
- **UI Framework**: Tailwind CSS + DaisyUI
- **Database**: MySQL (dengan dukungan untuk PostgreSQL dan SQLite)
- **Authentication**: Laravel Breeze

### Komponen Utama
1. **Dashboard Meja** - Menampilkan status meja secara visual
2. **Sistem Billing** - Menghitung durasi dan biaya otomatis
3. **Manajemen Produk** - Penambahan item tambahan dengan manajemen stok
4. **Sistem Pembayaran** - Proses transaksi dan cetak struk
5. **Pelaporan** - Laporan harian, bulanan, dan tahunan
6. **Sistem Booking** - Manajemen pemesanan meja di waktu mendatang

## 🔄 Alur Bisnis Utama

### 1. Proses Pemakaian Meja
```
Kasir memilih meja available → Sistem mengubah status meja ke occupied → 
Transaksi dibuat dengan started_at sekarang → Durasi dihitung secara real-time → 
Kasir menyelesaikan sesi → Sistem menghitung total biaya → 
Proses pembayaran → Status meja kembali ke available
```

### 2. Penambahan Item Tambahan
```
Kasir menambahkan item dari daftar produk → Sistem mengurangi stok → 
Item ditambahkan ke transaksi → Total transaksi diperbarui → 
Saat pembayaran, stok secara permanen berkurang
```

### 3. Proses Booking Meja
```
Kasir memilih tanggal dan jam booking → Sistem cek ketersediaan meja → 
Kasir memilih meja dan masukkan info pelanggan → Sistem buat booking dengan status reserved → 
Sistem ubah status meja menjadi reserved → Pada waktu booking tiba, kasir check-in pelanggan → 
Sistem buat transaksi baru dan ubah status meja menjadi occupied
```

## 🗃️ Struktur Database

Sistem menggunakan beberapa tabel utama:
- `users` - Informasi akun pengguna (kasir/admin)
- `tables` - Informasi meja (nama, tarif per jam, status, jenis)
- `products` - Produk tambahan (nama, harga, stok)
- `transactions` - Data transaksi (meja, kasir, durasi, total)
- `transaction_items` - Item tambahan dalam transaksi
- `inventory_transactions` - Catatan historis perubahan stok
- `bookings` - Data booking meja di waktu mendatang (pelanggan, tanggal, jam, status)

## 🔐 Keamanan dan Akses

- **Otentikasi**: Laravel Breeze dengan email/password
- **Otorisasi**: Role-based access (admin/cashier)
- **Validasi Input**: Validasi di setiap level aplikasi
- **Audit Trail**: Semua perubahan penting dicatat

## 📊 Fitur Laporan

- **Laporan Harian**: Ringkasan transaksi dan pendapatan harian
- **Laporan Bulanan**: Tren pendapatan dan penggunaan meja
- **Laporan Tahunan**: Analisis kinerja tahunan

## 🛠️ Fitur Teknis

### Penanganan Durasi
- Perhitungan durasi otomatis dari started_at ke ended_at
- Pembulatan durasi ke atas ke jam penuh terdekat
- Penanganan edge case seperti started_at di masa depan
- Fallback calculation jika duration_minutes = 0

### Manajemen Stok
- Pelacakan stok real-time
- Validasi ketersediaan sebelum transaksi
- Catatan historis semua perubahan stok
- Notifikasi stok rendah

## 📱 UI/UX

- **Dashboard Visual**: Grid meja dengan status warna
- **Antarmuka Touch-friendly**: Tombol besar dan responsif
- **Real-time Updates**: Informasi durasi dan biaya langsung diperbarui
- **Dark Mode Lengkap**: Mendukung tema gelap konsisten di seluruh antarmuka
- **Booking Management**: Antarmuka untuk mengelola pemesanan meja di waktu mendatang
- **Status Meja Lengkap**: Menampilkan status `available`, `occupied`, `maintenance`, dan `reserved`

## 🔄 Scalability dan Extensibility

Sistem dirancang untuk dapat dikembangkan dengan:
- Modul booking untuk pemesanan meja (sudah diimplementasikan)
- Integrasi printer thermal
- API untuk integrasi eksternal
- Multi-outlet support (di masa depan)