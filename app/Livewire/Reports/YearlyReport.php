<?php

namespace App\Livewire\Reports;

use App\Models\Transaction;
use App\Services\ReportService;
use App\Services\ExportService;
use Carbon\Carbon;
use Livewire\Component;

class YearlyReport extends Component
{
    public $year;
    public $yearlyDataChart = [];
    public $totalRevenue = 0;
    public $transactionCount = 0;
    public $avgTransactionValue = 0;

    // Don't store complex objects in public properties to avoid serialization errors
    // Use computed properties instead

    public function mount()
    {
        $this->year = Carbon::now()->year;
        $this->updateReport();
        $this->loadYearlyChartData();
    }

    public function updateReport()
    {
        $this->validate([
            'year' => 'required|integer|min:1900|max:2100',
        ]);

        $reportService = new ReportService();
        $reportData = $reportService->getYearlyReportData($this->year);

        $this->totalRevenue = $reportData['total_revenue'];
        $this->transactionCount = $reportData['transaction_count'];
        $this->avgTransactionValue = $reportData['avg_transaction_value'];
    }

    public function loadYearlyChartData()
    {
        $this->yearlyDataChart = $this->getYearlyDataForChart();
    }

    private function getYearlyDataForChart()
    {
        $reportService = new ReportService();
        $reportData = $reportService->getYearlyReportData($this->year);
        
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        
        $labels = [];
        $values = [];
        
        foreach ($months as $month) {
            $monthlyRevenue = 0;
            
            if (isset($reportData['monthly_data'][$month])) {
                $monthlyTransactions = $reportData['monthly_data'][$month];
                $monthlyRevenue = $monthlyTransactions->sum('total');
            }
            
            $labels[] = substr($month, 0, 3); // Use first 3 letters of the month
            $values[] = (int) $monthlyRevenue;
        }
        
        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    public function getTransactions()
    {
        $reportService = new ReportService();
        $reportData = $reportService->getYearlyReportData($this->year);
        return $reportData['transactions'];
    }

    public function getYearlyData()
    {
        $reportService = new ReportService();
        return $reportService->getYearlyReportData($this->year);
    }

    public function exportToCsv()
    {
        $reportService = new ReportService();
        $exportService = new ExportService();
        
        $reportData = $reportService->getYearlyReportData($this->year);
        $formattedData = $this->formatYearlyReportData($reportData); // Custom format for yearly report
        
        return $exportService->exportToCsv($formattedData, 'yearly_report_' . $this->year);
    }

    public function exportToExcel()
    {
        $reportService = new ReportService();
        $exportService = new ExportService();
        
        $reportData = $reportService->getYearlyReportData($this->year);
        $formattedData = $this->formatYearlyReportData($reportData); // Custom format for yearly report
        
        return $exportService->exportToExcel($formattedData, 'yearly_report_' . $this->year);
    }

    public function exportToPdf()
    {
        $reportService = new ReportService();
        $exportService = new ExportService();
        
        $reportData = $reportService->getYearlyReportData($this->year);
        $formattedData = $this->formatYearlyReportData($reportData); // Custom format for yearly report
        
        return $exportService->exportToPdf($formattedData, 'yearly_report_' . $this->year, [
            'Date', 'ID', 'Table', 'Cashier', 'Started At', 'Ended At', 'Duration (min)', 'Total', 'Payment Method'
        ], 'Yearly Report - ' . $this->year);
    }
    
    private function formatYearlyReportData($reportData)
    {
        $formatted = [];
        
        foreach ($reportData['transactions'] as $transaction) {
            $formatted[] = [
                'Date' => $transaction->created_at->format('Y-m-d'),
                'ID' => $transaction->id,
                'Table' => $transaction->table->name,
                'Cashier' => $transaction->user->name,
                'Started At' => $transaction->started_at->format('H:i'),
                'Ended At' => $transaction->ended_at ? $transaction->ended_at->format('H:i') : 'N/A',
                'Duration (min)' => $transaction->duration_minutes,
                'Total' => $transaction->total,
                'Payment Method' => $transaction->payment_method,
            ];
        }
        
        return $formatted;
    }

    public function render()
    {
        return view('livewire.reports.yearly-report');
    }
}