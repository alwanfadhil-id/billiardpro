# Laporan Tahunan dalam BilliardPro

## 📋 Gambaran Umum

Dokumen ini menjelaskan spesifikasi dan implementasi laporan tahunan dalam sistem BilliardPro.

## 📊 Struktur Laporan Tahunan

### Data yang Ditampilkan
Laporan tahunan berisi informasi berikut:
- Ringkasan pendapatan tahunan
- Total transaksi tahunan
- Rata-rata pendapatan bulanan
- Tren penggunaan meja tahunan
- Grafik pendapatan bulanan sepanjang tahun
- Bulan dengan pendapatan tertinggi dan terendah
- Statistik penggunaan meja per bulan

### Filter dan Parameter
- Tahun (opsional, default ke tahun saat ini)
- Rentang tanggal otomatis berdasarkan tahun yang dipilih

## 🔄 Komponen Laporan Tahunan

### YearlyReport Component
```php
// app/Livewire/Reports/YearlyReport.php
public $year; // Tahun yang dipilih
public $yearlyRevenue = 0; // Total pendapatan tahunan
public $yearlyTransactionCount = 0; // Jumlah transaksi tahunan
public $monthlyRevenueData = []; // Data grafik pendapatan bulanan
public $yearlyTransactions = []; // Daftar transaksi tahunan
public $monthlyBreakdown = []; // Breakdown pendapatan per bulan
```

## 🛠️ Fungsi-fungsi Utama

### 1. Update Laporan Tahunan
```php
public function updateReport()
{
    $this->validate([
        'year' => 'required|integer|min:2000|max:2100'
    ]);

    $startDate = Carbon::create($this->year, 1, 1)->startOfYear();
    $endDate = Carbon::create($this->year, 1, 1)->endOfYear();

    $this->yearlyTransactions = Transaction::with(['table', 'user', 'items.product'])
        ->whereBetween('created_at', [$startDate, $endDate])
        ->orderBy('created_at', 'desc')
        ->get();

    $this->yearlyRevenue = $this->yearlyTransactions->sum('total');
    $this->yearlyTransactionCount = $this->yearlyTransactions->count();
}
```

### 2. Load Data Grafik Tahunan
```php
private function getMonthlyRevenueDataForChart()
{
    $labels = [];
    $values = [];
    
    for ($month = 1; $month <= 12; $month++) {
        $monthStart = Carbon::create($this->year, $month, 1)->startOfMonth();
        $monthEnd = Carbon::create($this->year, $month, 1)->endOfMonth();
        
        $monthlyRevenue = Transaction::whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('status', 'completed')
            ->sum('total');
            
        $labels[] = Carbon::create($this->year, $month, 1)->format('M');
        $values[] = (int) $monthlyRevenue;
    }
    
    return [
        'labels' => $labels,
        'values' => $values
    ];
}
```

### 3. Load Breakdown Bulanan
```php
private function getMonthlyBreakdown()
{
    $breakdown = [];
    
    for ($month = 1; $month <= 12; $month++) {
        $monthStart = Carbon::create($this->year, $month, 1)->startOfMonth();
        $monthEnd = Carbon::create($this->year, $month, 1)->endOfMonth();
        
        $monthlyRevenue = Transaction::whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('status', 'completed')
            ->sum('total');
            
        $monthlyTransactions = Transaction::whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('status', 'completed')
            ->count();
            
        $breakdown[Carbon::create($this->year, $month, 1)->format('F')] = [
            'revenue' => $monthlyRevenue,
            'transactions' => $monthlyTransactions
        ];
    }
    
    return $breakdown;
}
```

## 📈 Fungsi Analitik Tahunan

### 1. Perhitungan Rata-rata Bulanan
```php
public function getAverageMonthlyRevenueProperty()
{
    return $this->yearlyRevenue / 12;
}
```

### 2. Identifikasi Bulan dengan Pendapatan Tertinggi dan Terendah
```php
public function getHighestRevenueMonth()
{
    $highestRevenue = 0;
    $highestMonth = null;
    
    for ($month = 1; $month <= 12; $month++) {
        $monthStart = Carbon::create($this->year, $month, 1)->startOfMonth();
        $monthEnd = Carbon::create($this->year, $month, 1)->endOfMonth();
        
        $monthlyRevenue = Transaction::whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('status', 'completed')
            ->sum('total');
            
        if ($monthlyRevenue > $highestRevenue) {
            $highestRevenue = $monthlyRevenue;
            $highestMonth = Carbon::create($this->year, $month, 1)->format('F');
        }
    }
    
    return ['month' => $highestMonth, 'revenue' => $highestRevenue];
}

public function getLowestRevenueMonth()
{
    $lowestRevenue = PHP_INT_MAX;
    $lowestMonth = null;
    
    for ($month = 1; $month <= 12; $month++) {
        $monthStart = Carbon::create($this->year, $month, 1)->startOfMonth();
        $monthEnd = Carbon::create($this->year, $month, 1)->endOfMonth();
        
        $monthlyRevenue = Transaction::whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('status', 'completed')
            ->sum('total');
            
        if ($monthlyRevenue < $lowestRevenue) {
            $lowestRevenue = $monthlyRevenue;
            $lowestMonth = Carbon::create($this->year, $month, 1)->format('F');
        }
    }
    
    return ['month' => $lowestMonth, 'revenue' => $lowestRevenue];
}
```

### 3. Persentase Pertumbuhan Tahunan
```php
public function getYearOverYearGrowth()
{
    $previousYear = $this->year - 1;
    
    $previousYearRevenue = Transaction::whereBetween('created_at', [
        Carbon::create($previousYear, 1, 1)->startOfYear(),
        Carbon::create($previousYear, 1, 1)->endOfYear()
    ])->where('status', 'completed')->sum('total');
    
    if ($previousYearRevenue == 0) {
        return $this->yearlyRevenue > 0 ? 100 : 0; // Jika tahun lalu 0 dan tahun ini > 0, growth 100%
    }
    
    return (($this->yearlyRevenue - $previousYearRevenue) / $previousYearRevenue) * 100;
}
```

## 📤 Fitur Ekspor Laporan Tahunan

### 1. Ekspor ke CSV
```php
public function exportToCsv()
{
    $reportService = new ReportService();
    $exportService = new ExportService();
    
    $reportData = $reportService->getYearlyReportData($this->year);
    $formattedData = $exportService->formatYearlyReportForExport($reportData);
    
    $fileName = 'yearly_report_' . $this->year;
    return $exportService->exportToCsv($formattedData, $fileName);
}
```

### 2. Ekspor ke Excel
```php
public function exportToExcel()
{
    $reportService = new ReportService();
    $exportService = new ExportService();
    
    $reportData = $reportService->getYearlyReportData($this->year);
    $formattedData = $exportService->formatYearlyReportForExport($reportData);
    
    $fileName = 'yearly_report_' . $this->year;
    return $exportService->exportToExcel($formattedData, $fileName);
}
```

### 3. Ekspor ke PDF
```php
public function exportToPdf()
{
    $reportService = new ReportService();
    $exportService = new ExportService();
    
    $reportData = $reportService->getYearlyReportData($this->year);
    $formattedData = $exportService->formatYearlyReportForExport($reportData);
    
    $title = 'Yearly Report - ' . $this->year;
    $fileName = 'yearly_report_' . $this->year;
    
    return $exportService->exportToPdf($formattedData, $fileName, [
        'ID', 'Table', 'Cashier', 'Date', 'Started At', 'Ended At', 'Duration (min)', 'Items', 'Total', 'Payment Method', 'Status'
    ], $title);
}
```

## 🔍 Fungsi Pencarian dan Filter

### 1. Filter Transaksi Berdasarkan Status
```php
public function getFilteredTransactionsForYear($status = null)
{
    $query = Transaction::with(['table', 'user', 'items.product']);
    
    $startDate = Carbon::create($this->year, 1, 1)->startOfYear();
    $endDate = Carbon::create($this->year, 1, 1)->endOfYear();
    
    $query->whereBetween('created_at', [$startDate, $endDate]);
    
    if ($status) {
        $query->where('status', $status);
    }
    
    return $query->orderBy('created_at', 'desc')->get();
}
```

### 2. Filter Berdasarkan Rentang Bulan
```php
public function getTransactionsForMonthRange($startMonth, $endMonth)
{
    $transactions = collect();
    
    for ($month = $startMonth; $month <= $endMonth; $month++) {
        $monthStart = Carbon::create($this->year, $month, 1)->startOfMonth();
        $monthEnd = Carbon::create($this->year, $month, 1)->endOfMonth();
        
        $monthlyTransactions = Transaction::whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('status', 'completed')
            ->get();
            
        $transactions = $transactions->concat($monthlyTransactions);
    }
    
    return $transactions;
}
```

## 🛡️ Validasi dan Keamanan

### 1. Hak Akses
- Hanya user dengan role admin yang bisa mengakses laporan tahunan
- Dilindungi oleh middleware `auth` dan `verified`

### 2. Validasi Input
- Tahun divalidasi dalam rentang wajar (2000-2100)
- Rentang tanggal dihitung otomatis berdasarkan input tahun

## 🧪 Unit Testing

### Test yang Harus Ada
1. **Validasi Input Tahun**: Memastikan input tahun divalidasi dengan benar
2. **Pengambilan Data Tahunan**: Memastikan data laporan diambil dengan benar dari database
3. **Perhitungan Total Tahunan**: Memastikan total pendapatan tahunan dihitung dengan benar
4. **Analitik Tahunan**: Memastikan fungsi analitik seperti `getHighestRevenueMonth` berfungsi dengan benar
5. **Ekspor Data**: Memastikan fitur ekspor berfungsi dengan benar

### Contoh Test
```php
public function test_yearly_report_calculates_correct_revenue()
{
    // Buat transaksi di tahun 2025
    $transaction1 = Transaction::factory()->create([
        'total' => 50000,
        'status' => 'completed',
        'created_at' => Carbon::create(2025, 6, 15)
    ]);
    
    $transaction2 = Transaction::factory()->create([
        'total' => 75000,
        'status' => 'completed',
        'created_at' => Carbon::create(2025, 12, 20)
    ]);
    
    $transaction3 = Transaction::factory()->create([
        'total' => 25000,
        'status' => 'ongoing', // Transaksi ongoing tidak dihitung dalam pendapatan
        'created_at' => Carbon::create(2025, 1, 25)
    ]);
    
    // Jalankan komponen laporan tahunan
    $report = Livewire::test(YearlyReport::class);
    
    // Set tahun
    $report->set('year', 2025);
    $report->call('updateReport');
    
    // Uji total pendapatan (hanya transaksi completed yang dihitung)
    $this->assertEquals(125000, $report->get('yearlyRevenue'));
    $this->assertEquals(2, $report->get('yearlyTransactionCount'));
}

public function test_yearly_report_identifies_highest_revenue_month()
{
    // Buat transaksi dengan pendapatan berbeda di beberapa bulan
    Transaction::factory()->create([
        'total' => 100000,
        'status' => 'completed',
        'created_at' => Carbon::create(2025, 3, 15) // Maret
    ]);
    
    Transaction::factory()->create([
        'total' => 200000,
        'status' => 'completed',
        'created_at' => Carbon::create(2025, 8, 20) // Agustus
    ]);
    
    Transaction::factory()->create([
        'total' => 50000,
        'status' => 'completed',
        'created_at' => Carbon::create(2025, 12, 25) // Desember
    ]);
    
    $report = Livewire::test(YearlyReport::class);
    $report->set('year', 2025);
    $report->call('updateReport');
    
    $highestMonth = $report->getHighestRevenueMonth();
    
    $this->assertEquals('August', $highestMonth['month']);
    $this->assertEquals(200000, $highestMonth['revenue']);
}