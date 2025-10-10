<?php

namespace App\Livewire\Reports;

use App\Models\Transaction;
use App\Services\ReportService;
use App\Services\ExportService;
use Carbon\Carbon;
use Livewire\Component;

class DailyReport extends Component
{
    public $date;
    public $totalRevenue = 0;
    public $transactions = [];
    public $revenueData = [];

    public function mount()
    {
        $this->date = Carbon::now()->format('Y-m-d');
        $this->updateReport();
        $this->loadRevenueChartData();
    }

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

    public function loadRevenueChartData()
    {
        $this->revenueData = $this->getRevenueDataForChart();
    }

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

    public function exportToCsv()
    {
        $reportService = new ReportService();
        $exportService = new ExportService();
        
        $reportData = $reportService->getDailyReportData($this->date);
        $formattedData = $exportService->formatDailyReportForExport($reportData);
        
        return $exportService->exportToCsv($formattedData, 'daily_report_' . $this->date);
    }

    public function exportToExcel()
    {
        $reportService = new ReportService();
        $exportService = new ExportService();
        
        $reportData = $reportService->getDailyReportData($this->date);
        $formattedData = $exportService->formatDailyReportForExport($reportData);
        
        return $exportService->exportToExcel($formattedData, 'daily_report_' . $this->date);
    }

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

    public function render()
    {
        return view('livewire.reports.daily-report');
    }
}