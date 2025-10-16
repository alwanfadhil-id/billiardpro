# 🧪 BILLIARDPRO - TESTING CHECKLIST

## Automated Tests Coverage

### Unit Tests
- [x] Method `calculateTotal()` di model Transaction → 1 menit durasi dibulatkan ke 1 jam
- [x] Method `calculateTotal()` di model Transaction → 61 menit durasi dibulatkan ke 2 jam  
- [x] Method `calculateTotal()` di model Transaction → dengan item tambahan
- [x] Validasi hourly_rate tidak boleh negatif → pengujian dengan nilai -10000 gagal sesuai ekspektasi
- [x] Validasi hourly_rate tidak boleh nol → pengujian dengan nilai 0 gagal sesuai ekspektasi
- [x] Validasi hourly_rate positif diterima → pengujian dengan nilai positif berhasil

### Feature Tests - Transaksi
- [x] Alur transaksi lengkap: login kasir → mulai sesi di meja available → tambah item → bayar → cek status meja jadi available dan transaksi tersimpan
- [x] Alur transaksi dengan durasi berbeda (90 menit → 2 jam billing)

### Feature Tests - Keamanan
- [x] Route `/tables/manage` hanya bisa diakses admin → cashier tidak bisa akses (403 Forbidden)
- [x] Route `/tables/manage` hanya bisa diakses admin → unauthenticated user redirect ke login
- [x] Admin bisa akses route `/tables/manage` → akses diperbolehkan
- [x] Admin bisa melakukan tindakan manajemen meja → akses diperbolehkan
- [x] Cashier tidak bisa melakukan tindakan manajemen meja → akses ditolak

### Manual Tests Still Needed
- [ ] Tambah meja baru → sukses
- [ ] Edit harga meja → harga baru berlaku untuk sesi berikutnya
- [ ] Set meja ke maintenance → tidak muncul di dashboard
- [ ] Mulai sesi → status meja berubah jadi merah
- [ ] Tunggu 2 menit → durasi di UI update
- [ ] Tambah Es Teh (Rp 10.000) → total naik
- [ ] Bayar Rp 120.000 untuk total Rp 110.000 → kembalian Rp 10.000
- [ ] Cetak struk → format rapi
- [ ] Hari ini ada 2 transaksi → laporan tunjukkan 2 transaksi
- [ ] Filter tanggal kemarin → data sesuai
- [ ] Login sebagai kasir → coba akses /tables/manage → redirect ke dashboard