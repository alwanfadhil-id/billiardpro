<?php

namespace App\Http\Livewire\Transactions;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Table;
use Illuminate\Support\Facades\Auth;

class StartSession extends Component
{
    public $tableId;
    public $table;
    public $startedAt;

    protected $rules = [
        'tableId' => 'required|exists:tables,id',
    ];

    public function mount($transaction = null)
    {
        if ($transaction) {
            $this->transaction = Transaction::find($transaction);
            if ($this->transaction) {
                $this->tableId = $this->transaction->table_id;
                $this->table = Table::find($this->tableId);
            }
        }
    }

    public function render()
    {
        if (!$this->tableId) {
            $availableTables = Table::where('status', 'available')->get();
            return view('livewire.transactions.start-session', [
                'availableTables' => $availableTables
            ])->layout('components.layouts.app');
        }

        $this->table = Table::find($this->tableId);
        
        return view('livewire.transactions.start-session-confirm', [
            'table' => $this->table
        ])->layout('components.layouts.app');
    }

    public function startSession()
    {
        $this->validate();
        
        $table = Table::findOrFail($this->tableId);
        
        // Check if table is available
        if ($table->status !== 'available') {
            session()->flash('error', 'Table is not available.');
            return;
        }
        
        // Create new transaction
        $transaction = Transaction::create([
            'table_id' => $this->tableId,
            'user_id' => Auth::id(),
            'started_at' => now(),
            'status' => 'ongoing',
            'total' => 0,
            'payment_method' => 'cash' // default value
        ]);
        
        // Update table status
        $table->update(['status' => 'occupied']);
        
        session()->flash('message', 'Session started successfully.');
        
        return redirect()->route('transactions.items', ['transaction' => $transaction->id]);
    }
}