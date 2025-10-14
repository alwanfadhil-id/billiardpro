<?php

namespace App\Livewire\Transactions;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\TransactionItem;

class PaymentProcess extends Component
{
    public $transactionId;
    public $transaction;
    public $paymentMethod = 'cash';
    public $amountReceived = 0;
    public $change = 0;
    
    public function mount($transaction)
    {
        $this->transactionId = $transaction;
        $this->transaction = Transaction::with(['table', 'items.product'])->find($transaction);
        
        if ($this->transaction) {
            // Update the transaction total to ensure it has the correct value
            $this->updateTransactionTotal();
            
            $this->amountReceived = $this->transaction->total;
            $this->calculateChange();
        }
    }
    
    public function render()
    {
        if (!$this->transaction) {
            session()->flash('error', 'Transaction not found.');
            return view('livewire.transactions.payment-process')->layout('components.layouts.app');
        }
        
        $transactionItems = $this->transaction->items;
        $tableCost = $this->calculateTableCost();
        
        return view('livewire.transactions.payment-process', [
            'transaction' => $this->transaction,
            'transactionItems' => $transactionItems,
            'tableCost' => $tableCost,
        ])->layout('components.layouts.app');
    }

    private function calculateTableCost()
    {
        if (!$this->transaction->ended_at) {
            // For ongoing transactions, calculate up to now
            $startedAt = $this->transaction->started_at;
            $now = now();
            $minutes = $startedAt->diffInMinutes($now);
        } else {
            // For completed transactions, use ended_at
            $startedAt = $this->transaction->started_at;
            $endedAt = $this->transaction->ended_at;
            $minutes = $startedAt->diffInMinutes($endedAt);
        }

        $hours = ceil($minutes / 60); // Round up to next hour
        $hourlyRate = $this->transaction->table->hourly_rate;
        return $hourlyRate * $hours;
    }
    
    public function updatedAmountReceived()
    {
        $this->calculateChange();
    }
    
    public function updatedPaymentMethod()
    {
        $this->calculateChange();
    }
    
    private function calculateChange()
    {
        if ($this->transaction) {
            $this->change = max(0, $this->amountReceived - $this->transaction->total);
        }
    }
    
    public function processPayment()
    {
        if (!$this->transaction) {
            session()->flash('error', 'Transaction not found.');
            return;
        }
        
        if ($this->amountReceived < $this->transaction->total) {
            session()->flash('error', 'Jumlah yang diterima kurang dari total tagihan.');
            return;
        }
        
        // Update transaction status to completed
        $this->transaction->update([
            'status' => 'completed',
            'payment_method' => $this->paymentMethod,
            'change_amount' => $this->change,
            'cash_received' => $this->amountReceived,
            'ended_at' => now(),
        ]);
        
        session()->flash('message', 'Pembayaran berhasil diproses.');
        
        // Redirect to receipt or back to dashboard
        return redirect()->route('transactions.receipt', ['transaction' => $this->transactionId]);
    }
    
    private function updateTransactionTotal()
    {
        $this->transaction->refresh();
        $tableCost = $this->calculateTableCost();
        $itemsCost = $this->transaction->items->sum('total_price');
        $this->transaction->update(['total' => $tableCost + $itemsCost]);
    }
    
    public function cancelPayment()
    {
        return redirect()->route('transactions.add-items', ['transaction' => $this->transactionId]);
    }
}