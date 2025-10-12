<?php

namespace App\Livewire\Dashboard;

use App\Models\Table;
use App\Models\Transaction;
use Livewire\Component;

class TableGrid extends Component
{
    public $search = '';
    public $filterStatus = 'all';

    protected $listeners = ['tableStatusUpdated' => '$refresh'];

    public function render()
    {
        // Query tables with filters
        $query = Table::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        $tables = $query->orderBy('name')->get();

        // Statistik
        $todayStart = now()->startOfDay();
        $todayRevenue = Transaction::where('status', 'completed')
            ->where('created_at', '>=', $todayStart)
            ->sum('total');

        $completedTransactions = Transaction::where('status', 'completed')
            ->where('created_at', '>=', $todayStart)
            ->count();

        $totalTables = Table::count();
        $availableTables = Table::where('status', 'available')->count();
        $occupiedTables = Table::where('status', 'occupied')->count();
        $maintenanceTables = Table::where('status', 'maintenance')->count();
        $activeSessions = Transaction::where('status', 'ongoing')->count();

        return view('livewire.dashboard.table-grid', compact(
            'tables',
            'todayRevenue',
            'completedTransactions',
            'totalTables',
            'availableTables',
            'occupiedTables',
            'maintenanceTables',
            'activeSessions'
        ));
    }

    public function startSession($tableId)
    {
        $table = Table::findOrFail($tableId);

        if ($table->status !== 'available') {
            $this->dispatch('alert', type: 'error', message: 'Meja tidak tersedia untuk digunakan.');
            return;
        }

        // Redirect ke halaman mulai sesi (atau buka modal)
        return redirect()->route('transactions.start', ['table' => $tableId]);
    }
}