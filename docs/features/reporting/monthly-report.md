# Laporan Bulanan dalam BilliardPro

## 📋 Gambaran Umum

Dokumen ini menjelaskan spesifikasi dan implementasi laporan bulanan dalam sistem BilliardPro.

## 📊 Struktur Laporan Bulanan

### Data yang Ditampilkan
Laporan bulanan berisi informasi berikut:
- Ringkasan pendapatan bulanan
- Total transaksi bulanan
- Rata-rata pendapatan harian
- Tren penggunaan meja bulanan
- Grafik pendapatan harian sepanjang bulan

### Filter dan Parameter
- Bulan (Januari-Desember)
- Tahun (opsional, default ke tahun saat ini)
- Rentang tanggal otomatis berdasarkan bulan yang dipilih

## 🔄 Komponen Laporan Bulanan

### MonthlyReport Component
```php
// app/Livewire/Reports/MonthlyReport.php
public $month; // Bulan yang dipilih (1-12)
public $year; // Tahun yang dipilih
public $monthlyRevenue = 0; // Total pendapatan bulanan
public $monthlyTransactionCount = 0; // Jumlah transaksi bulanan
public $dailyRevenueData = []; // Data grafik pendapatan harian
public $monthlyTransactions = []; // Daftar transaksi bulanan
```

## 🛠️ Fungsi-fungsi Utama

### 1. Update Laporan Bulanan
```php
public function updateReport()
{
    $this->validate([
        'month' => 'required|integer|min:1|max:12',
        'year' => 'required|integer|min:2000|max:2100'
    ]);

    $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
    $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();

    $this->monthlyTransactions = Transaction::with(['table', 'user', 'items.product'])
        ->whereBetween('created_at', [$startDate, $endDate])
        ->orderBy('created_at', 'desc')
        ->get();

    $this->monthlyRevenue = $this->monthlyTransactions->sum('total');
    $this->monthlyTransactionCount = $this->monthlyTransactions->count();
}
```

### 2. Load Data Grafik Bulanan
```php
private function getDailyRevenueDataForChart()
{
    $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
    $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();
    
    $labels = [];
    $values = [];
    
    $currentDate = $startDate->copy();
    while ($currentDate <= $endDate) {
        $dayStart = $currentDate->copy()->startOfDay();
        $dayEnd = $currentDate->copy()->endOfDay();
        
        $dailyRevenue = Transaction::whereBetween('created_at', [$dayStart, $dayEnd])
            ->where('status', 'completed')
            ->sum('total');
            
        $labels[] = $currentDate->format('d M');
        $values[] = (int) $dailyRevenue;
        
        $currentDate->addDay();
    }
    
    return [
        'labels' => $labels,
        'values' => $values
    ];
}
```

## 📈 Fungsi Analitik Bulanan

### 1. Perhitungan Rata-rata Harian
```php
public function getAverageDailyRevenueProperty()
{
    if ($this->daysInMonth === 0) return 0;
    return $this->monthlyRevenue / $this->daysInMonth;
}
```

### 2. Identifikasi Hari dengan Pendapatan Tertinggi
```php
public function getHighestRevenueDay()
{
    $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
    $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();
    
    $highestRevenue = 0;
    $highestRevenueDate = null;
    
    $currentDate = $startDate->copy();
    while ($currentDate <= $endDate) {
        $dayStart = $currentDate->copy()->startOfDay();
        $dayEnd = $currentDate->copy()->endOfDay();
        
        $dailyRevenue = Transaction::whereBetween('created_at', [$dayStart, $dayEnd])
            ->where('status', 'completed')
            ->sum('total');
            
        if ($dailyRevenue > $highestRevenue) {
            $highestRevenue = $dailyRevenue;
            $highestRevenueDate = $currentDate->format('Y-m-d');
        }
        
        $currentDate->addDay();
    }
    
    return $highestRevenueDate;
}
```

## 📤 Fitur Ekspor Laporan Bulanan

### 1. Ekspor ke CSV
```php
public function exportToCsv()
{
    $reportService = new ReportService();
    $exportService = new ExportService();
    
    $reportData = $reportService->getMonthlyReportData($this->month, $this->year);
    $formattedData = $exportService->formatMonthlyReportForExport($reportData);
    
    $fileName = 'monthly_report_' . $this->year . '-' . str_pad($this->month, 2, '0', STR_PAD_LEFT);
    return $exportService->exportToCsv($formattedData, $fileName);
}
```

### 2. Ekspor ke Excel
```php
public function exportToExcel()
{
    $reportService = new ReportService();
    $exportService = new ExportService();
    
    $reportData = $reportService->getMonthlyReportData($this->month, $this->year);
    $formattedData = $exportService->formatMonthlyReportForExport($reportData);
    
    $fileName = 'monthly_report_' . $this->year . '-' . str_pad($this->month, 2, '0', STR_PAD_LEFT);
    return $exportService->exportToExcel($formattedData, $fileName);
}
```

### 3. Ekspor ke PDF
```php
public function exportToPdf()
{
    $reportService = new ReportService();
    $exportService = new ExportService();
    
    $reportData = $reportService->getMonthlyReportData($this->month, $this->year);
    $formattedData = $exportService->formatMonthlyReportForExport($reportData);
    
    $title = 'Monthly Report - ' . Carbon::create($this->year, $this->month, 1)->format('F Y');
    $fileName = 'monthly_report_' . $this->year . '-' . str_pad($this->month, 2, '0', STR_PAD_LEFT);
    
    return $exportService->exportToPdf($formattedData, $fileName, [
        'ID', 'Table', 'Cashier', 'Started At', 'Ended At', 'Duration (min)', 'Items', 'Total', 'Payment Method', 'Status'
    ], $title);
}
```

## 🔍 Fungsi Pencarian dan Filter

### 1. Filter Transaksi Berdasarkan Status
```php
public function getFilteredTransactionsForMonth($status = null)
{
    $query = Transaction::with(['table', 'user', 'items.product']);
    
    $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
    $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();
    
    $query->whereBetween('created_at', [$startDate, $endDate]);
    
    if ($status) {
        $query->where('status', $status);
    }
    
    return $query->orderBy('created_at', 'desc')->get();
}
```

### 2. Filter Berdasarkan Meja
```php
public function getTransactionsForTable($tableId)
{
    $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
    $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();
    
    return Transaction::where('table_id', $tableId)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->where('status', 'completed')
        ->sum('total');
}
```

## 🛡️ Validasi dan Keamanan

### 1. Hak Akses
- Hanya user dengan role admin yang bisa mengakses laporan bulanan
- Dilindungi oleh middleware `auth` dan `verified`

### 2. Validasi Input
- Bulan divalidasi antara 1-12
- Tahun divalidasi dalam rentang wajar (2000-2100)
- Rentang tanggal dihitung otomatis berdasarkan input bulan dan tahun

## 🧪 Unit Testing

### Test yang Harus Ada
1. **Validasi Input Bulan dan Tahun**: Memastikan input bulan dan tahun divalidasi dengan benar
2. **Pengambilan Data Bulanan**: Memastikan data laporan diambil dengan benar dari database
3. **Perhitungan Total Bulanan**: Memastikan total pendapatan bulanan dihitung dengan benar
4. **Filter Status Transaksi**: Memastikan filter transaksi berdasarkan status berfungsi dengan benar
5. **Ekspor Data**: Memastikan fitur ekspor berfungsi dengan benar

### Contoh Test
```php
public function test_monthly_report_calculates_correct_revenue()
{
    // Buat transaksi di bulan Januari 2025
    $transaction1 = Transaction::factory()->create([
        'total' => 50000,
        'status' => 'completed',
        'created_at' => Carbon::create(2025, 1, 15)
    ]);
    
    $transaction2 = Transaction::factory()->create([
        'total' => 75000,
        'status' => 'completed',
        'created_at' => Carbon::create(2025, 1, 20)
    ]);
    
    $transaction3 = Transaction::factory()->create([
        'total' => 25000,
        'status' => 'ongoing', // Transaksi ongoing tidak dihitung dalam pendapatan
        'created_at' => Carbon::create(2025, 1, 25)
    ]);
    
    // Jalankan komponen laporan bulanan
    $report = Livewire::test(MonthlyReport::class);
    
    // Set bulan dan tahun
    $report->set('month', 1);
    $report->set('year', 2025);
    $report->call('updateReport');
    
    // Uji total pendapatan (hanya transaksi completed yang dihitung)
    $this->assertEquals(125000, $report->get('monthlyRevenue'));
    $this->assertEquals(2, $report->get('monthlyTransactionCount'));
}
```

## 🔄 Update Terbaru

Dokumentasi ini mencerminkan pengembangan laporan bulanan sebagai tambahan dari versi awal sistem yang hanya memiliki laporan harian. Fitur ini memberikan wawasan yang lebih luas terhadap tren bisnis.