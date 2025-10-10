<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use App\Models\Transaction;
use Carbon\Carbon;

class DailyReport extends Component
{
    public $date;

    public function mount()
    {
        $this->date = Carbon::now()->format('Y-m-d');
    }

    public function render()
    {
        $transactions = Transaction::whereDate('created_at', $this->date)
            ->where('status', 'completed')
            ->with(['table', 'user', 'items'])
            ->get();
        
        $totalRevenue = $transactions->sum('total');
        $totalTransactions = $transactions->count();
        
        return view('livewire.reports.daily-report', [
            'transactions' => $transactions,
            'totalRevenue' => $totalRevenue,
            'totalTransactions' => $totalTransactions,
            'date' => $this->date
        ])->layout('components.layouts.app');
    }

    public function monthly()
    {
        $monthlyData = Transaction::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->where('status', 'completed')
            ->selectRaw('DATE(created_at) as date, SUM(total) as daily_total, COUNT(*) as transaction_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('livewire.reports.monthly-report', [
            'monthlyData' => $monthlyData
        ])->layout('components.layouts.app');
    }

    public function yearly()
    {
        $yearlyData = Transaction::whereYear('created_at', Carbon::now()->year)
            ->where('status', 'completed')
            ->selectRaw('MONTH(created_at) as month, SUM(total) as monthly_total, COUNT(*) as transaction_count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('livewire.reports.yearly-report', [
            'yearlyData' => $yearlyData
        ])->layout('components.layouts.app');
    }
}