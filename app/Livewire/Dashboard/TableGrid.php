<?php

namespace App\Livewire\Dashboard;

use App\Models\Table;
use App\Models\Transaction;
use Livewire\Component;

class TableGrid extends Component
{
    public $search = '';
    public $filterStatus = 'all';
    public $selectedTable = null;
    public $showModal = false;
    public $showEditForm = false; // Property to toggle between view and edit mode
    public $name;
    public $hourly_rate;
    public $status;

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

    public function selectTable($tableId)
    {
        $this->selectedTable = Table::with(['transactions' => function($query) {
            $query->where('status', 'ongoing')->latest();
        }])->findOrFail($tableId);
        
        // Initialize form fields with current table values
        $this->name = $this->selectedTable->name;
        $this->hourly_rate = $this->selectedTable->hourly_rate;
        $this->status = $this->selectedTable->status;
        
        $this->showModal = true;
        $this->showEditForm = false; // Start with view mode
    }

    public function closeModal()
    {
        // Jika dalam mode edit, munculkan konfirmasi atau kembali ke mode view
        if ($this->showEditForm) {
            $this->showEditForm = false;
            return;
        }

        $this->showModal = false;
        $this->showEditForm = false;
        $this->selectedTable = null;
        $this->name = null;
        $this->hourly_rate = null;
        $this->status = null;
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
    
    public function markAsOccupied($tableId)
    {
        $table = Table::findOrFail($tableId);
        
        if ($table->status !== 'available') {
            $this->dispatch('alert', type: 'error', message: 'Meja tidak tersedia untuk digunakan.');
            return;
        }
        
        $table->update(['status' => 'occupied']);
        $this->dispatch('tableStatusUpdated');
        $this->closeModal();
        
        // Create a new transaction
        $transaction = Transaction::create([
            'table_id' => $table->id,
            'user_id' => auth()->id(),
            'started_at' => now(),
            'status' => 'ongoing',
        ]);
        
        $this->dispatch('alert', type: 'success', message: 'Status meja telah diubah menjadi terpakai.');
    }
    
    public function markAsAvailable($tableId)
    {
        $table = Table::findOrFail($tableId);
        
        // Update any ongoing transactions to completed
        $ongoingTransaction = $table->transactions()->where('status', 'ongoing')->first();
        if ($ongoingTransaction) {
            $ongoingTransaction->update([
                'ended_at' => now(),
                'status' => 'completed'
            ]);
        }
        
        $table->update(['status' => 'available']);
        $this->dispatch('tableStatusUpdated');
        $this->closeModal();
        
        $this->dispatch('alert', type: 'success', message: 'Status meja telah diubah menjadi tersedia.');
    }
    
    public function markAsMaintenance($tableId)
    {
        $table = Table::findOrFail($tableId);
        
        // If the table was occupied, complete the transaction
        $ongoingTransaction = $table->transactions()->where('status', 'ongoing')->first();
        if ($ongoingTransaction) {
            $ongoingTransaction->update([
                'ended_at' => now(),
                'status' => 'completed'
            ]);
        }
        
        $table->update(['status' => 'maintenance']);
        $this->dispatch('tableStatusUpdated');
        $this->closeModal();
        
        $this->dispatch('alert', type: 'success', message: 'Status meja telah diubah menjadi maintenance.');
    }
    
    public function toggleEditForm()
    {
        $this->showEditForm = !$this->showEditForm;
        
        // Jika kembali ke mode view dari mode edit, kembalikan nilai-nilai asli
        if (!$this->showEditForm && $this->selectedTable) {
            $this->name = $this->selectedTable->name;
            $this->hourly_rate = $this->selectedTable->hourly_rate;
            $this->status = $this->selectedTable->status;
        }
    }
    
    public function updateTable()
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'hourly_rate' => 'required|numeric|min:0',
            'status' => 'required|in:available,occupied,maintenance',
        ]);
        
        $this->selectedTable->update([
            'name' => $this->name,
            'hourly_rate' => $this->hourly_rate,
            'status' => $this->status,
        ]);
        
        $this->dispatch('tableStatusUpdated');
        $this->showEditForm = false;
        $this->dispatch('alert', type: 'success', message: 'Data meja berhasil diperbarui.');
    }
    
    // Method to support Livewire polling for real-time updates
    public function pollingRefresh()
    {
        $this->dispatch('tableStatusUpdated');
    }
}