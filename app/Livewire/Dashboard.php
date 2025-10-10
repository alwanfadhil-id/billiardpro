<?php

namespace App\Livewire;

use App\Models\Table;
use App\Models\Transaction;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

class Dashboard extends Component
{
    public $todayRevenue = 0;
    public $completedTransactions = 0;
    public $availableTables = 0;
    public $totalTables = 0;
    public $occupiedTables = 0;
    public $activeSessions = 0;
    public $maintenanceTables = 0;

    #[Layout('components.layouts.app')]
    public function render()
    {
        $this->loadSummaryData();
        return view('livewire.dashboard-content');
    }

    public function loadSummaryData()
    {
        // Calculate today's revenue (from completed transactions today)
        $this->todayRevenue = Transaction::whereDate('ended_at', date('Y-m-d'))
            ->where('status', 'completed')
            ->sum('total');
            
        // Count completed transactions today
        $this->completedTransactions = Transaction::whereDate('ended_at', date('Y-m-d'))
            ->where('status', 'completed')
            ->count();
            
        // Count available tables
        $this->availableTables = Table::where('status', 'available')->count();
        
        // Count total tables
        $this->totalTables = Table::count();
        
        // Count occupied tables
        $this->occupiedTables = Table::where('status', 'occupied')->count();
        
        // Count maintenance tables
        $this->maintenanceTables = Table::where('status', 'maintenance')->count();
        
        // Count active sessions (ongoing transactions)
        $this->activeSessions = Transaction::where('status', 'ongoing')->count();
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}