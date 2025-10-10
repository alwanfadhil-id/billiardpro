<?php

namespace App\Http\Livewire\Transactions;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Table;
use Illuminate\Support\Facades\Auth;

class PaymentProcess extends Component
{
    public $transactionId;
    public $transaction;
    public $cashReceived;
    public $paymentMethod = 'cash';
    public $changeAmount = 0;
    public $total;

    protected $rules = [
        'cashReceived' => 'required_if:paymentMethod,cash|numeric|min:0',
        'paymentMethod' => 'required|in:cash,qris,card',
    ];

    public function mount($transaction)
    {
        $this->transactionId = $transaction;
        $this->transaction = Transaction::with(['table', 'user', 'items'])->find($transaction);
        
        if ($this->transaction) {
            $this->total = $this->calculateTotalCost();
        }
    }

    public function render()
    {
        if (!$this->transaction) {
            session()->flash('error', 'Transaction not found.');
            return view('livewire.transactions.payment-process')->layout('components.layouts.app');
        }
        
        return view('livewire.transactions.payment-process', [
            'transaction' => $this->transaction,
            'total' => $this->total
        ])->layout('components.layouts.app');
    }

    public function processPayment()
    {
        $this->validate();
        
        $this->transaction->refresh(); // Refresh to get latest total
        $this->total = $this->calculateTotalCost();
        
        // Calculate change if payment method is cash
        if ($this->paymentMethod === 'cash') {
            if ($this->cashReceived < $this->total) {
                session()->flash('error', 'Insufficient cash received.');
                return;
            }
            $this->changeAmount = $this->cashReceived - $this->total;
        } else {
            // For non-cash payments, no change calculation needed
            $this->cashReceived = $this->total;
            $this->changeAmount = 0;
        }
        
        // Calculate final duration and total
        $startedAt = $this->transaction->started_at;
        $endedAt = now();
        $minutes = $startedAt->diffInMinutes($endedAt);
        $durationHours = ceil($minutes / 60);
        
        // Update transaction
        $this->transaction->update([
            'ended_at' => $endedAt,
            'duration_minutes' => $minutes,
            'total' => $this->total,
            'payment_method' => $this->paymentMethod,
            'cash_received' => $this->cashReceived,
            'change_amount' => $this->changeAmount,
            'status' => 'completed',
        ]);
        
        // Update table status back to available
        $table = Table::find($this->transaction->table_id);
        if ($table) {
            $table->update(['status' => 'available']);
        }
        
        session()->flash('message', 'Payment processed successfully.');
    }

    public function receipt()
    {
        if (!$this->transaction) {
            abort(404);
        }
        
        // In a real application, you would generate a PDF receipt
        return view('livewire.transactions.receipt', [
            'transaction' => $this->transaction
        ])->layout('components.layouts.app');
    }

    private function calculateTotalCost()
    {
        $startedAt = $this->transaction->started_at;
        $endedAt = now();
        $minutes = $startedAt->diffInMinutes($endedAt);
        $hours = ceil($minutes / 60); // Round up to next hour
        $tableCost = $this->transaction->table->hourly_rate * $hours;
        $itemsCost = $this->transaction->items->sum('total_price');
        
        return $tableCost + $itemsCost;
    }
}