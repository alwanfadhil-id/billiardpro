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
    public $showAvailableTableModal = false; // New property for available table popup
    public $showEditForm = false; // Property to toggle between view and edit mode
    public $name;
    public $type;
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

        $tables = $query->with(['transactions' => function($q) {
            $q->where('status', 'ongoing')->latest();
        }])->orderBy('name')->get();

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
        $table = Table::with(['transactions' => function($query) {
            $query->where('status', 'ongoing')->latest();
        }])->findOrFail($tableId);
        
        $this->selectedTable = $table;
        
        // Initialize form fields with current table values
        $this->name = $this->selectedTable->name;
        $this->type = $this->selectedTable->type;
        $this->hourly_rate = $this->selectedTable->hourly_rate;
        $this->status = $this->selectedTable->status;
        
        // Check if the table is available to show the special modal
        if ($table->status === 'available') {
            $this->showAvailableTableModal = true;
        } else {
            $this->showModal = true;
        }
        
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
        $this->showAvailableTableModal = false;
        $this->showEditForm = false;
        $this->selectedTable = null;
        $this->name = null;
        $this->type = null;
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

        // Create a new transaction to track the session
        $transaction = Transaction::create([
            'table_id' => $table->id,
            'user_id' => auth()->id(),
            'started_at' => now(),
            'status' => 'ongoing',
            'total' => 0, // Will be calculated at the end
        ]);

        // Update table status to occupied
        $table->update(['status' => 'occupied']);
        
        // Dispatch event to update table status across components
        $this->dispatch('tableStatusUpdated');
        
        // Redirect to the transaction page to continue the session
        return redirect()->route('transactions.add-items', ['transaction' => $transaction->id]);
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
            'total' => 0, // Will be calculated at the end
        ]);
        
        $this->dispatch('alert', type: 'success', message: 'Status meja telah diubah menjadi terpakai.');
    }
    
    public function markAsAvailable($tableId)
    {
        $table = Table::findOrFail($tableId);
        
        // Update any ongoing transactions - calculate duration and total, but don't complete the payment yet
        $ongoingTransaction = $table->transactions()->where('status', 'ongoing')->first();
        if ($ongoingTransaction) {
            // Calculate duration and total cost for the transaction
            $duration = now()->diffInMinutes($ongoingTransaction->started_at);
            $ratePerHour = $table->hourly_rate;
            $ratePerMinute = $ratePerHour / 60;
            $total = $duration * $ratePerMinute;
            
            // Update transaction with end time and calculated total, but keep status as ongoing
            // Payment and status change to 'completed' will happen on payment page
            $ongoingTransaction->update([
                'ended_at' => now(),
                'duration_minutes' => $duration,
                'total' => $total
            ]);
            
            // Now redirect to payment page to complete the transaction
            return redirect()->route('transactions.payment', ['transaction' => $ongoingTransaction->id]);
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
            $this->type = $this->selectedTable->type;
            $this->hourly_rate = $this->selectedTable->hourly_rate;
            $this->status = $this->selectedTable->status;
            
            // If the table was available and we're going back to view mode, keep the available table modal
            if ($this->selectedTable->status === 'available') {
                $this->showAvailableTableModal = true;
                $this->showModal = false;
            }
        }
    }
    
    public function updateTable()
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:biasa,premium,vip',
            'hourly_rate' => 'required|numeric|min:0',
            'status' => 'required|in:available,occupied,maintenance',
        ]);
        
        $this->selectedTable->update([
            'name' => $this->name,
            'type' => $this->type,
            'hourly_rate' => $this->hourly_rate,
            'status' => $this->status,
        ]);
        
        $this->dispatch('tableStatusUpdated');
        $this->showEditForm = false;
        
        // After updating, show the appropriate modal based on status
        if ($this->selectedTable->status === 'available') {
            $this->showAvailableTableModal = true;
            $this->showModal = false;
        } else {
            $this->showModal = true;
            $this->showAvailableTableModal = false;
        }
        
        $this->dispatch('alert', type: 'success', message: 'Data meja berhasil diperbarui.');
    }
    
    public function getDurationForTable($table)
    {
        $ongoingTransaction = $table->transactions->first();
        
        if ($ongoingTransaction) {
            $startedAt = $ongoingTransaction->started_at;
            $now = now();
            $minutes = $startedAt->diffInMinutes($now);
            
            $hours = intdiv($minutes, 60);
            $remainingMinutes = $minutes % 60;
            
            return [
                'hours' => $hours,
                'minutes' => $remainingMinutes,
                'total_minutes' => $minutes
            ];
        }
        
        return [
            'hours' => 0,
            'minutes' => 0,
            'total_minutes' => 0
        ];
    }

    // Method to support Livewire polling for real-time updates
    public function pollingRefresh()
    {
        $this->dispatch('tableStatusUpdated');
    }
}