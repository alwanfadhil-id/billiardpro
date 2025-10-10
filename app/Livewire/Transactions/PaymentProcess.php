<?php

namespace App\Livewire\Transactions;

use App\Models\Transaction;
use App\Models\Table;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class PaymentProcess extends Component
{
    public $transactionId;
    public $transaction;
    public $cashReceived = 0;
    public $paymentMethod = 'cash';
    public $changeAmount = 0;
    public $totalAmount = 0;

    public function mount($transactionId = null)
    {
        $this->transactionId = $transactionId;
        if ($this->transactionId) {
            $this->transaction = Transaction::with(['table', 'items.product', 'user'])->find($this->transactionId);
            if (!$this->transaction) {
                session()->flash('error', 'Transaction not found.');
                return redirect()->route('dashboard');
            }
            
            if ($this->transaction->isCompleted()) {
                session()->flash('error', 'This transaction is already completed.');
                return redirect()->route('dashboard');
            }
            
            // Set the current time as ended_at for calculation
            $this->transaction->ended_at = now();
            $this->calculateTotal();
        }
    }

    public function calculateTotal()
    {
        if (!$this->transaction) {
            return 0;
        }

        // Calculate duration in minutes
        $start = $this->transaction->started_at;
        $end = $this->transaction->ended_at;
        $durationMinutes = $start->diffInMinutes($end);

        // Round up to nearest hour
        $hours = ceil($durationMinutes / 60);

        // Calculate table cost
        $tableCost = $this->transaction->table->hourly_rate * $hours;

        // Calculate items cost
        $itemsCost = $this->transaction->items->sum('total_price');

        $this->totalAmount = $tableCost + $itemsCost;

        // Calculate change if cash received is set
        if ($this->cashReceived > 0) {
            $this->changeAmount = max(0, $this->cashReceived - $this->totalAmount);
        }
    }

    public function updatedCashReceived()
    {
        $this->calculateChange();
    }

    public function calculateChange()
    {
        if ($this->cashReceived >= $this->totalAmount) {
            $this->changeAmount = $this->cashReceived - $this->totalAmount;
        } else {
            $this->changeAmount = 0;
        }
    }

    public function processPayment()
    {
        $this->validate([
            'cashReceived' => 'required|numeric|min:' . $this->totalAmount,
            'paymentMethod' => 'required|string'
        ]);

        \DB::beginTransaction();

        try {
            // Calculate final values
            $this->calculateTotal();
            
            // Calculate duration in minutes
            $start = $this->transaction->started_at;
            $end = now();
            $durationMinutes = $start->diffInMinutes($end);

            // Update transaction
            $this->transaction->update([
                'ended_at' => $end,
                'duration_minutes' => $durationMinutes,
                'total' => $this->totalAmount,
                'payment_method' => $this->paymentMethod,
                'cash_received' => $this->cashReceived,
                'change_amount' => $this->changeAmount,
                'status' => 'completed'
            ]);

            // Change table status to available
            $table = $this->transaction->table;
            $table->update(['status' => 'available']);

            \DB::commit();

            session()->flash('message', 'Payment processed successfully!');
            
            // Redirect to receipt
            return redirect()->route('transactions.receipt', ['transactionId' => $this->transactionId]);

        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Update ended_at for real-time calculation display
        if ($this->transaction && !$this->transaction->isCompleted()) {
            $this->transaction->ended_at = now();
            $this->calculateTotal();
        }

        return view('livewire.transactions.payment-process', [
            'table' => $this->transaction?->table,
            'items' => $this->transaction ? $this->transaction->items()->with('product')->get() : collect([])
        ]);
    }
}