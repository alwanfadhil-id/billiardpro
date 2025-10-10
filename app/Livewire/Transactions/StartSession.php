<?php

namespace App\Livewire\Transactions;

use App\Models\Table;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class StartSession extends Component
{
    public $tableId;
    public $table;
    public $showModal = false;

    protected $listeners = [
        'startSession' => 'openModal',
    ];

    public function mount($tableId = null)
    {
        $this->tableId = $tableId;
        if ($this->tableId) {
            $this->table = Table::find($this->tableId);
        }
    }

    public function openModal($tableId)
    {
        $this->tableId = $tableId;
        $this->table = Table::find($this->tableId);
        
        if (!$this->table) {
            session()->flash('error', 'Table not found.');
            return;
        }

        if (!$this->table->isAvailable()) {
            session()->flash('error', 'Table is not available for booking.');
            return;
        }

        $this->showModal = true;
    }

    public function startSession()
    {
        if (!$this->table || !$this->table->isAvailable()) {
            session()->flash('error', 'Table is not available for booking.');
            return;
        }

        \DB::beginTransaction();
        
        try {
            // Update table status to occupied
            $this->table->update(['status' => 'occupied']);

            // Create a new transaction
            $transaction = $this->table->transactions()->create([
                'user_id' => Auth::id(),
                'started_at' => now(),
                'status' => 'ongoing',
                'payment_method' => 'cash', // Default to cash, can be changed later
            ]);

            \DB::commit();

            // Close modal
            $this->showModal = false;
            
            // Flash success message
            session()->flash('message', 'Session started successfully for ' . $this->table->name);
            
            // Redirect to add items page
            return redirect()->route('transactions.add-items', ['transactionId' => $transaction->id]);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Failed to start session: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.transactions.start-session');
    }
}