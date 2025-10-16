<?php

namespace App\Livewire\Dashboard;

use App\Models\Transaction;
use App\Models\Table;
use App\Services\ReportService;
use Livewire\Component;

class Analytics extends Component
{
    public $dailyRevenue = 0;
    public $weeklyRevenue = 0;
    public $monthlyRevenue = 0;
    public $yearlyRevenue = 0;
    
    public $dailyTransactions = 0;
    public $weeklyTransactions = 0;
    public $monthlyTransactions = 0;
    public $yearlyTransactions = 0;
    
    public $revenueGrowth = 0;
    public $transactionGrowth = 0;
    
    public $recentTransactions = [];
    public $topProducts = [];
    public $tableUsageStats = [];

    public function mount()
    {
        $this->loadAnalytics();
    }

    public function loadAnalytics()
    {
        $reportService = new ReportService();
        
        // Load daily analytics
        $todayData = $reportService->getDailyReportData();
        $this->dailyRevenue = $todayData['total_revenue'];
        $this->dailyTransactions = $todayData['transaction_count'];
        
        // Calculate weekly analytics (last 7 days)
        $oneWeekAgo = now()->subWeek();
        $weeklyTransactions = Transaction::where('created_at', '>=', $oneWeekAgo)
            ->where('status', 'completed')
            ->get();
            
        $this->weeklyRevenue = $weeklyTransactions->sum('total');
        $this->weeklyTransactions = $weeklyTransactions->count();
        
        // Load monthly analytics
        $monthlyData = $reportService->getMonthlyReportData();
        $this->monthlyRevenue = $monthlyData['total_revenue'];
        $this->monthlyTransactions = $monthlyData['transaction_count'];
        
        // Load yearly analytics
        $yearlyData = $reportService->getYearlyReportData();
        $this->yearlyRevenue = $yearlyData['total_revenue'];
        $this->yearlyTransactions = $yearlyData['transaction_count'];
        
        // Calculate growth (comparing with previous week)
        $twoWeeksAgo = now()->subWeeks(2);
        $oneWeekAgo = now()->subWeek();
        
        $prevWeekRevenue = Transaction::where('created_at', '>=', $twoWeeksAgo)
            ->where('created_at', '<', $oneWeekAgo)
            ->where('status', 'completed')
            ->sum('total');
            
        $currentWeekRevenue = $this->weeklyRevenue;
        
        if ($prevWeekRevenue > 0) {
            $this->revenueGrowth = round((($currentWeekRevenue - $prevWeekRevenue) / $prevWeekRevenue) * 100, 2);
        } else {
            $this->revenueGrowth = $currentWeekRevenue > 0 ? 100 : 0;
        }
        
        // Calculate transaction growth compared to previous week
        $prevWeekTransactions = Transaction::where('created_at', '>=', $twoWeeksAgo)
            ->where('created_at', '<', $oneWeekAgo)
            ->where('status', 'completed')
            ->count();
            
        $currentWeekTransactions = $this->weeklyTransactions;
        
        if ($prevWeekTransactions > 0) {
            $this->transactionGrowth = round((($currentWeekTransactions - $prevWeekTransactions) / $prevWeekTransactions) * 100, 2);
        } else {
            $this->transactionGrowth = $currentWeekTransactions > 0 ? 100 : 0;
        }
        
        // Load additional data
        $this->loadRecentTransactions();
        $this->loadTopProducts();
        $this->loadTableUsageStats();
    }
    
    private function loadRecentTransactions()
    {
        $this->recentTransactions = Transaction::with(['table', 'user'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }
    
    private function loadTopProducts()
    {
        $reportService = new ReportService();
        $productSales = $reportService->getProductSalesReport(
            now()->subDays(30)->startOfDay(), 
            now()->endOfDay()
        );
        
        $this->topProducts = $productSales['products']
            ->sortByDesc('quantity_sold')
            ->take(5);
    }
    
    private function loadTableUsageStats()
    {
        $reportService = new ReportService();
        $tableUsage = $reportService->getTableUsageReport(
            now()->subDays(30)->startOfDay(), 
            now()->endOfDay()
        );
        
        $this->tableUsageStats = $tableUsage['tables']
            ->sortByDesc('usage_count')
            ->take(5);
    }

    public function render()
    {
        return view('livewire.dashboard.analytics');
    }
}