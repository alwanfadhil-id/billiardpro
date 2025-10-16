# API Endpoints dalam BilliardPro

## 📋 Gambaran Umum

Dokumen ini menjelaskan endpoint-endpoint API yang tersedia dalam sistem BilliardPro. API ini digunakan untuk integrasi eksternal dan sebagai backend untuk komponen frontend.

## 🔐 Otentikasi

Semua endpoint memerlukan otentikasi kecuali dinyatakan lain:
- Gunakan Laravel Sanctum untuk API token
- Header: `Authorization: Bearer {token}`
- Atau session-based auth untuk interaksi dalam aplikasi

## 🏷️ Kategori Endpoint

### 1. Authentication
Endpoint untuk proses otentikasi dan manajemen session.

#### POST /api/login
- **Deskripsi**: Login pengguna
- **Request**: 
  ```json
  {
    "email": "user@example.com",
    "password": "password"
  }
  ```
- **Response**:
  ```json
  {
    "token": "api_token_here",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "role": "cashier"
    }
  }
  ```

#### POST /api/logout
- **Deskripsi**: Logout pengguna
- **Response**: 200 OK

### 2. Meja (Tables)

#### GET /api/tables
- **Deskripsi**: Dapatkan semua meja
- **Response**:
  ```json
  [
    {
      "id": 1,
      "name": "Meja 1",
      "type": "biasa",
      "hourly_rate": 10000,
      "status": "available",
      "created_at": "2023-01-01T00:00:00Z"
    }
  ]
  ```

#### GET /api/tables/{id}
- **Deskripsi**: Dapatkan detail meja tertentu
- **Response**: Data meja seperti di atas

#### POST /api/tables
- **Deskripsi**: Buat meja baru (hanya admin)
- **Request**:
  ```json
  {
    "name": "Meja VIP 1",
    "type": "vip",
    "hourly_rate": 20000
  }
  ```

#### PUT /api/tables/{id}
- **Deskripsi**: Update meja (hanya admin)
- **Request**: Seperti POST

#### DELETE /api/tables/{id}
- **Deskripsi**: Hapus meja (hanya admin)

### 3. Produk (Products)

#### GET /api/products
- **Deskripsi**: Dapatkan semua produk
- **Response**:
  ```json
  [
    {
      "id": 1,
      "name": "Es Teh Manis",
      "price": 5000,
      "category": "Minuman",
      "stock_quantity": 20,
      "min_stock_level": 5,
      "created_at": "2023-01-01T00:00:00Z"
    }
  ]
  ```

#### POST /api/products
- **Deskripsi**: Buat produk baru (hanya admin)
- **Request**:
  ```json
  {
    "name": "Kopi Susu",
    "price": 8000,
    "category": "Minuman",
    "stock_quantity": 30,
    "min_stock_level": 3
  }
  ```

#### PUT /api/products/{id}
- **Deskripsi**: Update produk (hanya admin)
- **Request**: Seperti POST

#### DELETE /api/products/{id}
- **Deskripsi**: Hapus produk (hanya admin)

### 4. Transaksi (Transactions)

#### GET /api/transactions
- **Deskripsi**: Dapatkan daftar transaksi
- **Parameter Query**:
  - `status` (optional): ongoing, completed, cancelled
  - `table_id` (optional): filter by table
  - `date` (optional): filter by date
- **Response**:
  ```json
  [
    {
      "id": 1,
      "table_id": 1,
      "user_id": 2,
      "started_at": "2023-01-01T10:00:00Z",
      "ended_at": "2023-01-01T12:00:00Z",
      "duration_minutes": 120,
      "total": 20000,
      "payment_method": "cash",
      "status": "completed",
      "created_at": "2023-01-01T00:00:00Z",
      "items": [
        {
          "id": 1,
          "product_id": 1,
          "quantity": 2,
          "price_per_item": 5000,
          "total_price": 10000
        }
      ]
    }
  ]
  ```

#### GET /api/transactions/{id}
- **Deskripsi**: Dapatkan detail transaksi
- **Response**: Seperti di atas, satu objek

#### POST /api/transactions
- **Deskripsi**: Buat transaksi baru (hanya cashier)
- **Validasi**: `started_at` harus `before_or_equal:now`
- **Request**:
  ```json
  {
    "table_id": 1,
    "started_at": "2023-01-01T10:00:00Z"
  }
  ```

#### PUT /api/transactions/{id}
- **Deskripsi**: Update transaksi (hanya cashier)
- **Request**:
  ```json
  {
    "ended_at": "2023-01-01T12:00:00Z",
    "payment_method": "cash",
    "cash_received": 25000,
    "status": "completed"
  }
  ```

#### PATCH /api/transactions/{id}/add-item
- **Deskripsi**: Tambah item ke transaksi
- **Request**:
  ```json
  {
    "product_id": 1,
    "quantity": 2
  }
  ```

#### DELETE /api/transaction-items/{id}
- **Deskripsi**: Hapus item dari transaksi (sebelum pembayaran)

### 5. Laporan (Reports)

#### GET /api/reports/daily
- **Deskripsi**: Dapatkan laporan harian
- **Parameter Query**:
  - `date` (optional): tanggal dalam format Y-m-d (default: today)
- **Response**:
  ```json
  {
    "date": "2023-01-01",
    "total_revenue": 150000,
    "total_transactions": 5,
    "transactions": [
      // Daftar transaksi
    ]
  }
  ```

#### GET /api/reports/monthly
- **Deskripsi**: Dapatkan laporan bulanan
- **Parameter Query**:
  - `month` (optional): bulan (1-12) (default: current month)
  - `year` (optional): tahun (default: current year)
- **Response**:
  ```json
  {
    "month": 1,
    "year": 2023,
    "total_revenue": 1500000,
    "total_transactions": 30,
    "daily_breakdown": {
      "2023-01-01": 150000,
      "2023-01-02": 120000,
      // dll
    }
  }
  ```

#### GET /api/reports/yearly
- **Deskripsi**: Dapatkan laporan tahunan
- **Parameter Query**:
  - `year` (optional): tahun (default: current year)
- **Response**:
  ```json
  {
    "year": 2023,
    "total_revenue": 18000000,
    "total_transactions": 365,
    "monthly_breakdown": {
      "January": 1500000,
      "February": 1200000,
      // dll
    }
  }
  ```

### 6. Pengguna (Users)

#### GET /api/users
- **Deskripsi**: Dapatkan daftar pengguna (hanya admin)
- **Response**:
  ```json
  [
    {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "role": "admin",
      "created_at": "2023-01-01T00:00:00Z"
    }
  ]
  ```

#### POST /api/users
- **Deskripsi**: Buat pengguna baru (hanya admin)
- **Request**:
  ```json
  {
    "name": "Cashier User",
    "email": "cashier@example.com",
    "password": "secure_password",
    "role": "cashier"
  }
  ```

#### PUT /api/users/{id}
- **Deskripsi**: Update pengguna (hanya admin)
- **Request**: Seperti POST (tanpa password wajib jika tidak diganti)

#### DELETE /api/users/{id}
- **Deskripsi**: Hapus pengguna (hanya admin)

## ⚠️ Validasi dan Error Handling

### Validasi Umum
- Semua input divalidasi sesuai kebutuhan
- Error response format:
  ```json
  {
    "message": "The given data was invalid.",
    "errors": {
      "field_name": [
        "Error message here"
      ]
    }
  }
  ```

### Kode Status HTTP
- `200`: Sukses untuk GET
- `201`: Sukses untuk POST (resource dibuat)
- `204`: Sukses untuk DELETE (tanpa response body)
- `400`: Bad request (input tidak valid)
- `401`: Unauthorized (belum login)
- `403`: Forbidden (tidak punya hak akses)
- `404`: Resource tidak ditemukan
- `422`: Unprocessable entity (validasi gagal)
- `500`: Server error

## 🔒 Keamanan

### Hak Akses
- Beberapa endpoint hanya bisa diakses oleh admin
- Endpoint transaksi hanya bisa diakses oleh kasir atau admin
- Gunakan policy Laravel untuk otorisasi lanjutan

### Pembatasan Rate
- Implementasi rate limiting untuk mencegah abuse
- Default: 60 request per menit per IP

### Validasi Input
- Validasi semua input sesuai kebutuhan
- Sanitasi data sebelum disimpan ke database
- Validasi `'before_or_equal:now'` untuk waktu
- Validasi ketersediaan stok untuk produk

## 🧪 Contoh Penggunaan API

### PHP (dengan cURL atau Guzzle)
```php
$client = new \GuzzleHttp\Client();

$response = $client->request('GET', 'http://localhost:8000/api/transactions', [
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
    ]
]);

$data = json_decode($response->getBody(), true);
```

### JavaScript (dengan Axios)
```javascript
const response = await axios.get('/api/transactions', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
    }
});

const transactions = response.data;
```

## 🔄 Update Terbaru

- Endpoint untuk manajemen stok produk
- Validasi `'before_or_equal:now'` untuk field `started_at`
- Endpoint untuk menambahkan item ke transaksi
- Endpoint untuk laporan harian, bulanan, dan tahunan
- Endpoint untuk manajemen pengguna (admin only)
- Implementasi hak akses berdasarkan role