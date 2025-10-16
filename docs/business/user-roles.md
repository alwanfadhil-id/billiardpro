# Peran Pengguna dalam Sistem BilliardPro

## 📋 Gambaran Umum

Sistem BilliardPro memiliki dua peran pengguna utama dengan hak akses yang berbeda-beda sesuai dengan kebutuhan operasional bisnis biliar.

## 👤 Jenis Peran Pengguna

### 1. Admin
**Level Akses**: Penuh

**Fungsi Utama**:
- Mengelola pengguna (tambah, edit, hapus akun)
- Mengelola meja (tambah, edit, hapus, atur tarif)
- Mengelola produk (tambah, edit, hapus, atur stok)
- Mengakses semua laporan (harian, bulanan, tahunan)
- Mengelola pengaturan sistem
- Mengakses semua transaksi dan detailnya

**Hak Akses Spesifik**:
- `/users` - Manajemen pengguna
- `/tables/manage` - Manajemen meja
- `/products` - Manajemen produk
- `/reports` - Laporan harian
- `/reports/monthly` - Laporan bulanan
- `/reports/yearly` - Laporan tahunan
- `/settings` - Pengaturan sistem

### 2. Cashier (Kasir)
**Level Akses**: Terbatas

**Fungsi Utama**:
- Memulai dan mengakhiri sesi meja
- Menambahkan item tambahan ke transaksi
- Memproses pembayaran
- Mengakses laporan harian (terbatas ke transaksi mereka sendiri)
- Mengubah status meja (kecuali manajemen meja secara keseluruhan)

**Hak Akses Spesifik**:
- `/dashboard` - Dashboard utama untuk mengelola meja
- `/transactions/add-items/{transaction}` - Menambahkan item ke transaksi
- `/transactions/payment/{transaction}` - Memproses pembayaran
- Fungsi manajemen meja dasar melalui modal di dashboard

## 🔐 Implementasi Otorisasi

### Dalam Kode
```php
// Dalam Livewire components
public function mount()
{
    if (!auth()->user()->isAdmin()) {
        abort(403, 'Unauthorized access');
    }
}

// Dalam model User.php
public function isAdmin()
{
    return $this->role === 'admin';
}

public function isCashier()
{
    return $this->role === 'cashier';
}
```

### Dalam Middleware
Beberapa route dilindungi berdasarkan role:
```php
// routes/web.php
Route::get('/tables/manage', TableForm::class)
    ->middleware('can:manage-users') // Hanya admin
    ->name('tables.manage');
```

## 🛡️ Prinsip Keamanan

### 1. Principle of Least Privilege
- Pengguna hanya diberikan hak akses minimum yang dibutuhkan untuk menjalankan fungsinya

### 2. Segregasi Tanggung Jawab
- Admin menangani manajemen sistem
- Kasir menangani operasional harian

### 3. Audit Trail
- Semua tindakan tercatat dengan user ID untuk tracking

## 🔄 Validasi Hak Akses

### Pada Setiap Request
- Middleware otentikasi memastikan user login
- Cek role dilakukan sebelum eksekusi operasi penting

### Di UI
- Tombol dan menu yang tidak diizinkan disembunyikan berdasarkan role
- Akses ke page tertentu dicegat jika tidak memiliki hak

## 🧪 Unit Testing

### Test Hak Akses
```php
public function test_admin_can_access_table_management()
{
    $admin = User::factory()->create(['role' => 'admin']);
    
    Livewire::actingAs($admin)
        ->test(TableForm::class)
        ->assertSuccessful();
}

public function test_cashier_cannot_access_table_management()
{
    $cashier = User::factory()->create(['role' => 'cashier']);
    
    $this->actingAs($cashier)
        ->get('/tables/manage')
        ->assertForbidden();
}
```

## 🔄 Update Terbaru

Sistem role-based access control ini dirancang untuk:
- Menjaga keamanan data transaksi
- Mencegah akses tidak sah ke fungsi administratif
- Membantu audit trail dengan mencatat peran pengguna
- Memisahkan tanggung jawab antara manajemen dan operasional