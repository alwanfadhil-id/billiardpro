# Fitur Pelaporan dalam BilliardPro

## 📋 Gambaran Umum

Dokumen ini menjelaskan fitur pelaporan yang tersedia dalam sistem BilliardPro, termasuk laporan harian, bulanan, dan tahunan.

## 📊 Jenis-jenis Laporan

### 1. Laporan Harian (`DailyReport`)
- Menampilkan ringkasan transaksi dan pendapatan harian
- Menampilkan grafik pendapatan 7 hari terakhir
- Menyediakan fitur ekspor (CSV, Excel, PDF)

### 2. Laporan Bulanan (`MonthlyReport`)
- Menampilkan ringkasan transaksi dan pendapatan bulanan
- Menampilkan grafik pendapatan bulanan
- Menyediakan fitur ekspor (CSV, Excel, PDF)

### 3. Laporan Tahunan (`YearlyReport`)
- Menampilkan ringkasan transaksi dan pendapatan tahunan
- Menampilkan grafik pendapatan tahunan
- Menyediakan fitur ekspor (CSV, Excel, PDF)

## 🔄 Struktur Data Laporan

### Data yang Ditampilkan
Setiap laporan berisi informasi berikut:
- ID Transaksi
- Nama Meja
- Nama Kasir
- Waktu Mulai
- Waktu Selesai
- Durasi (menit)
- Item yang Dibeli
- Total Pembayaran
- Metode Pembayaran
- Status Transaksi

## 🛠️ Komponen Laporan

### 1. DailyReport Component
```php
// app/Livewire/Reports/DailyReport.php
public $date; // Tanggal yang dipilih
public $revenueData = []; // Data grafik pendapatan
public $totalRevenue = 0; // Total pendapatan harian
public $transactions = []; // Daftar transaksi harian
```

### 2. MonthlyReport Component
```php
// app/Livewire/Reports/MonthlyReport.php
public $month; // Bulan yang dipilih
public $year; // Tahun yang dipilih
public $monthlyRevenue = 0; // Total pendapatan bulanan
public $monthlyTransactions = []; // Daftar transaksi bulanan
```

### 3. YearlyReport Component
```php
// app/Livewire/Reports/YearlyReport.php
public $year; // Tahun yang dipilih
public $yearlyRevenue = 0; // Total pendapatan tahunan
public $yearlyTransactions = []; // Daftar transaksi tahunan
```

## 📈 Fungsi-fungsi Laporan

### 1. Load Data Laporan
```php
// Dalam DailyReport
public function updateReport()
{
    $this->validate([
        'date' => 'required|date',
    ]);

    $startDate = Carbon::parse($this->date)->startOfDay();
    $endDate = Carbon::parse($this->date)->endOfDay();

    $this->transactions = Transaction::with(['table', 'user', 'items.product'])
        ->whereBetween('created_at', [$startDate, $endDate])
        ->orderBy('created_at', 'desc')
        ->get();

    $this->totalRevenue = $this->transactions->sum('total');
}
```

### 2. Load Data Grafik
```php
// Dalam DailyReport
private function getRevenueDataForChart()
{
    $data = [];
    $labels = [];
    $values = [];
    
    for ($i = 6; $i >= 0; $i--) {
        $date = Carbon::now()->subDays($i);
        $startDate = $date->copy()->startOfDay();
        $endDate = $date->copy()->endOfDay();
        
        $dailyRevenue = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('total');
            
        $labels[] = $date->format('M d');
        $values[] = (int) $dailyRevenue;
    }
    
    return [
        'labels' => $labels,
        'values' => $values
    ];
}
```

## 📤 Fitur Ekspor

### 1. Ekspor ke CSV
```php
public function exportToCsv()
{
    $reportService = new ReportService();
    $exportService = new ExportService();
    
    $reportData = $reportService->getDailyReportData($this->date);
    $formattedData = $exportService->formatDailyReportForExport($reportData);
    
    return $exportService->exportToCsv($formattedData, 'daily_report_' . $this->date);
}
```

### 2. Ekspor ke Excel
```php
public function exportToExcel()
{
    $reportService = new ReportService();
    $exportService = new ExportService();
    
    $reportData = $reportService->getDailyReportData($this->date);
    $formattedData = $exportService->formatDailyReportForExport($reportData);
    
    return $exportService->exportToExcel($formattedData, 'daily_report_' . $this->date);
}
```

### 3. Ekspor ke PDF
```php
public function exportToPdf()
{
    $reportService = new ReportService();
    $exportService = new ExportService();
    
    $reportData = $reportService->getDailyReportData($this->date);
    $formattedData = $exportService->formatDailyReportForExport($reportData);
    
    return $exportService->exportToPdf($formattedData, 'daily_report_' . $this->date, [
        'ID', 'Table', 'Cashier', 'Started At', 'Ended At', 'Duration (min)', 'Items', 'Total', 'Payment Method', 'Status'
    ], 'Daily Report - ' . $this->date);
}
```

## 🛡️ Validasi dan Keamanan

### 1. Hak Akses
- Hanya user dengan role tertentu (admin) yang bisa mengakses fitur laporan
- Dilindungi oleh middleware `auth` dan `verified`

### 2. Validasi Input
- Tanggal yang dimasukkan divalidasi sebagai tanggal yang valid
- Rentang tanggal diatur untuk mencegah permintaan data yang terlalu besar sekaligus

## 🧪 Unit Testing

### Test yang Harus Ada
1. **Validasi Input**: Memastikan input tanggal divalidasi dengan benar
2. **Pengambilan Data**: Memastikan data laporan diambil dengan benar dari database
3. **Perhitungan Total**: Memastikan total pendapatan dihitung dengan benar
4. **Ekspor Data**: Memastikan fitur ekspor berfungsi dengan benar

### Contoh Test
```php
public function test_daily_report_calculates_correct_revenue()
{
    // Buat transaksi dengan status completed
    $transaction1 = Transaction::factory()->create([
        'total' => 50000,
        'status' => 'completed',
        'created_at' => Carbon::today()
    ]);
    
    $transaction2 = Transaction::factory()->create([
        'total' => 75000,
        'status' => 'completed',
        'created_at' => Carbon::today()
    ]);
    
    $transaction3 = Transaction::factory()->create([
        'total' => 25000,
        'status' => 'ongoing', // Transaksi ongoing tidak dihitung dalam pendapatan
        'created_at' => Carbon::today()
    ]);
    
    // Jalankan komponen laporan
    $report = Livewire::test(DailyReport::class);
    
    // Update tanggal ke hari ini
    $report->set('date', Carbon::today()->format('Y-m-d'));
    $report->call('updateReport');
    
    // Uji total pendapatan (hanya transaksi completed yang dihitung)
    $this->assertEquals(125000, $report->get('totalRevenue'));
}
```

## 🔄 Update Terbaru

Dokumentasi ini mencerminkan fitur pelaporan yang dikembangkan setelah versi awal sistem, termasuk:
- Tambahnya laporan bulanan dan tahunan selain laporan harian
- Fungsi ekspor ke berbagai format (CSV, Excel, PDF)
- Integrasi dengan layanan `ReportService` dan `ExportService`
- Fitur grafik pendapatan historis