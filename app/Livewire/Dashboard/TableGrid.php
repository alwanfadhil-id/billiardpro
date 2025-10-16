<?php

namespace App\Livewire\Dashboard;

use App\Models\Table;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
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
    
    public function updatedSearch()
    {
        // The search is handled in the render method, so we just need to refresh
    }

    public function updatedFilterStatus()
    {
        // The filter is handled in the render method, so we just need to refresh
    }
    
    public function clearSearch()
    {
        $this->search = '';
        $this->filterStatus = 'all';
    }

    public function render()
    {
        // Query tables with filters
        $query = Table::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . trim($this->search) . '%');
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
            $rawDuration = abs(now()->diffInMinutes($ongoingTransaction->started_at)); // Gunakan abs untuk workaround bug diffInMinutes
            $duration = max(0, intval($rawDuration)); // Pastikan durasi tidak minus dan adalah integer
            $ratePerHour = $table->hourly_rate;
            $ratePerMinute = $ratePerHour / 60;
            $total = $duration * $ratePerMinute;
            
            // Log untuk debugging durasi sebelum update
            Log::info('TableGrid markAsAvailable: Updating transaction', [
                'transaction_id' => $ongoingTransaction->id,
                'table_id' => $table->id,
                'calculated_duration' => $duration,
                'calculated_total' => $total,
                'raw_duration' => $rawDuration,
                'started_at' => $ongoingTransaction->started_at,
                'ended_at_for_update' => now(),
            ]);

            // Update transaction with end time and calculated total, but keep status as ongoing
            // Payment and status change to 'completed' will happen on payment page
            $ongoingTransaction->update([
                'ended_at' => now(),
                'duration_minutes' => $duration,
                'total' => $total
            ]);

            // Log untuk debugging durasi setelah update
            Log::info('TableGrid markAsAvailable: Transaction updated', [
                'transaction_id' => $ongoingTransaction->id,
                'duration_minutes_after_update' => $ongoingTransaction->fresh()->duration_minutes, // Ambil fresh untuk pastikan
                'ended_at_after_update' => $ongoingTransaction->fresh()->ended_at,
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
            // Calculate duration and total cost for the transaction as a fallback
            $rawDuration = now()->diffInMinutes($ongoingTransaction->started_at);
            $duration = max(0, intval($rawDuration)); // Apply same logic as TableGrid

            // Log untuk debugging durasi sebelum update di markAsMaintenance
            Log::info('TableGrid markAsMaintenance: Updating transaction', [
                'transaction_id' => $ongoingTransaction->id,
                'table_id' => $table->id,
                'calculated_duration' => $duration,
                'raw_duration' => $rawDuration,
                'started_at' => $ongoingTransaction->started_at,
                'ended_at_for_update' => now(),
            ]);

            $ongoingTransaction->update([
                'ended_at' => now(),
                'duration_minutes' => $duration,
                'status' => 'completed'
            ]);

            // Log untuk debugging durasi setelah update di markAsMaintenance
            Log::info('TableGrid markAsMaintenance: Transaction updated', [
                'transaction_id' => $ongoingTransaction->id,
                'duration_minutes_after_update' => $ongoingTransaction->fresh()->duration_minutes,
                'ended_at_after_update' => $ongoingTransaction->fresh()->ended_at,
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
            } else {
                $this->showModal = true;
                $this->showAvailableTableModal = false;
            }
        } else {
            // Ketika masuk ke mode edit, selalu gunakan modal umum (showModal)
            $this->showModal = true;
            $this->showAvailableTableModal = false;
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