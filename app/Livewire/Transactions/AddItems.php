<?php

namespace App\Livewire\Transactions;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class AddItems extends Component
{
    public $transactionId;
    public $transaction;
    public $selectedProduct;
    public $quantity = 1;
    public $products;
    public $search = '';

    protected $rules = [
        'selectedProduct' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
    ];

    public function mount($transaction)
    {
        $this->transactionId = $transaction;
        $this->transaction = Transaction::with(['table', 'items.product'])->find($transaction);
        $this->loadProducts();
        
        // Update transaction total when component mounts to ensure accurate calculation
        $this->updateTransactionTotal();
    }

    public function render()
    {
        if (!$this->transaction) {
            session()->flash('error', 'Transaction not found.');
            return view('livewire.transactions.add-items')->layout('components.layouts.app');
        }
        
        $transactionItems = $this->transaction->items;
        $this->loadProducts();
        
        return view('livewire.transactions.add-items', [
            'transaction' => $this->transaction,
            'transactionItems' => $transactionItems,
        ])->layout('components.layouts.app');
    }

    private function loadProducts()
    {
        $query = Product::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $this->products = $query->get();
    }

    public function updatingSearch()
    {
        $this->loadProducts();
    }

    public function addItem()
    {
        $this->validate();
        
        $product = Product::find($this->selectedProduct);
        $pricePerItem = $product->price;
        $totalPrice = $pricePerItem * $this->quantity;
        
        TransactionItem::create([
            'transaction_id' => $this->transactionId,
            'product_id' => $this->selectedProduct,
            'quantity' => $this->quantity,
            'price_per_item' => $pricePerItem,
            'total_price' => $totalPrice,
        ]);
        
        // Update transaction total
        $this->updateTransactionTotal();
        
        session()->flash('message', 'Item added successfully.');
        $this->reset(['selectedProduct', 'quantity']);
    }

    public function removeFromTransaction($itemId)
    {
        $item = TransactionItem::find($itemId);
        if ($item && $item->transaction_id == $this->transactionId) {
            $item->delete();
            
            // Update transaction total
            $this->updateTransactionTotal();
            
            session()->flash('message', 'Item removed successfully.');
        }
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

        $hours = max(1, ceil($minutes / 60)); // Round up to next hour with minimum 1 hour
        $hourlyRate = $this->transaction->table->hourly_rate;
        return $hourlyRate * $hours;
    }
    
    private function updateTransactionTotal()
    {
        $this->transaction->refresh();
        $tableCost = $this->calculateTableCost();
        $itemsCost = $this->transaction->items->sum('total_price');
        $this->transaction->update(['total' => $tableCost + $itemsCost]);
    }
    
    public function getRealTimeTotal()
    {
        $this->transaction->refresh();
        $tableCost = $this->calculateTableCost();
        $itemsCost = $this->transaction->items->sum('total_price');
        return $tableCost + $itemsCost;
    }

    public function goToPayment()
    {
        return redirect()->route('transactions.payment', ['transaction' => $this->transactionId]);
    }

    public function selectProduct($productId)
    {
        $this->selectedProduct = $productId;
        $this->quantity = 1; // Reset quantity to 1 when selecting a product
    }
}