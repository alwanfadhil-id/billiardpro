<?php

namespace App\Livewire\Reports;

use App\Models\Transaction;
use App\Services\ReportService;
use App\Services\ExportService;
use Carbon\Carbon;
use Livewire\Component;

class MonthlyReport extends Component
{
    public $month;
    public $year;
    public $monthlyDataChart = [];
    public $totalRevenue = 0;
    public $transactionCount = 0;
    public $avgTransactionValue = 0;

    // Don't store complex objects in public properties to avoid serialization errors
    // Use computed properties instead

    public function mount()
    {
        $this->month = Carbon::now()->month;
        $this->year = Carbon::now()->year;
        $this->updateReport();
        $this->loadMonthlyChartData();
    }

    public function updateReport()
    {
        $this->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:1900|max:2100',
        ]);

        $reportService = new ReportService();
        $reportData = $reportService->getMonthlyReportData($this->month, $this->year);

        $this->totalRevenue = $reportData['total_revenue'];
        $this->transactionCount = $reportData['transaction_count'];
        $this->avgTransactionValue = $reportData['avg_transaction_value'];
    }

    public function loadMonthlyChartData()
    {
        $this->monthlyDataChart = $this->getMonthlyDataForChart();
    }

    private function getMonthlyDataForChart()
    {
        $reportService = new ReportService();
        $reportData = $reportService->getMonthlyReportData($this->month, $this->year);
        
        $labels = [];
        $values = [];
        
        // Get the number of days in the selected month
        $daysInMonth = Carbon::create($this->year, $this->month, 1)->daysInMonth;
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateKey = str_pad($day, 2, '0', STR_PAD_LEFT);
            $dailyRevenue = 0;
            
            if (isset($reportData['daily_data'][$dateKey])) {
                $dailyTransactions = $reportData['daily_data'][$dateKey];
                $dailyRevenue = $dailyTransactions->sum('total');
            }
            
            $labels[] = $day;
            $values[] = (int) $dailyRevenue;
        }
        
        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    public function getTransactions()
    {
        $reportService = new ReportService();
        $reportData = $reportService->getMonthlyReportData($this->month, $this->year);
        return $reportData['transactions'];
    }

    public function getMonthlyData()
    {
        $reportService = new ReportService();
        return $reportService->getMonthlyReportData($this->month, $this->year);
    }

    public function exportToCsv()
    {
        $reportService = new ReportService();
        $exportService = new ExportService();
        
        $reportData = $reportService->getMonthlyReportData($this->month, $this->year);
        $formattedData = $exportService->formatMonthlyReportForExport($reportData);
        
        return $exportService->exportToCsv($formattedData, 'monthly_report_' . $this->year . '_' . str_pad($this->month, 2, '0', STR_PAD_LEFT));
    }

    public function exportToExcel()
    {
        $reportService = new ReportService();
        $exportService = new ExportService();
        
        $reportData = $reportService->getMonthlyReportData($this->month, $this->year);
        $formattedData = $exportService->formatMonthlyReportForExport($reportData);
        
        return $exportService->exportToExcel($formattedData, 'monthly_report_' . $this->year . '_' . str_pad($this->month, 2, '0', STR_PAD_LEFT));
    }

    public function exportToPdf()
    {
        $reportService = new ReportService();
        $exportService = new ExportService();
        
        $reportData = $reportService->getMonthlyReportData($this->month, $this->year);
        $formattedData = $exportService->formatMonthlyReportForExport($reportData);
        
        return $exportService->exportToPdf($formattedData, 'monthly_report_' . $this->year . '_' . str_pad($this->month, 2, '0', STR_PAD_LEFT), [
            'Date', 'ID', 'Table', 'Cashier', 'Started At', 'Ended At', 'Duration (min)', 'Total', 'Payment Method'
        ], 'Monthly Report - ' . Carbon::create()->month($this->month)->format('F') . ' ' . $this->year);
    }

    public function render()
    {
        return view('livewire.reports.monthly-report');
    }
}