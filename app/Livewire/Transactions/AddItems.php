<?php

namespace App\Livewire\Transactions;

use App\Models\Transaction;
use App\Models\Product;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class AddItems extends Component
{
    public $transactionId;
    public $transaction;
    public $products;
    public $cart = [];
    public $search = '';

    public function mount($transactionId = null)
    {
        $this->transactionId = $transactionId;
        if ($this->transactionId) {
            $this->transaction = Transaction::with(['table', 'items.product'])->find($this->transactionId);
            if ($this->transaction->isCompleted()) {
                session()->flash('error', 'This transaction is already completed.');
                return redirect()->route('dashboard');
            }
        }
        $this->loadProducts();
    }

    public function loadProducts()
    {
        if ($this->search) {
            $this->products = Product::where('name', 'like', '%' . $this->search . '%')->get();
        } else {
            $this->products = Product::all();
        }
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        if (!$product) {
            session()->flash('error', 'Product not found.');
            return;
        }

        $cartKey = $product->id;
        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['quantity']++;
        } else {
            $this->cart[$cartKey] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1
            ];
        }

        $this->calculateCartTotals();
    }

    public function removeFromCart($productId)
    {
        unset($this->cart[$productId]);
        $this->calculateCartTotals();
    }

    public function updateQuantity($productId, $newQuantity)
    {
        if ($newQuantity <= 0) {
            $this->removeFromCart($productId);
        } else {
            $this->cart[$productId]['quantity'] = $newQuantity;
            $this->calculateCartTotals();
        }
    }

    public function calculateCartTotals()
    {
        // This is just for UI updates, the actual total will be calculated on checkout
        foreach ($this->cart as $key => $item) {
            $this->cart[$key]['total'] = $item['price'] * $item['quantity'];
        }
    }

    public function proceedToPayment()
    {
        if (empty($this->cart) && $this->transaction->items->count() === 0) {
            // If no items and no existing items, go to payment directly
            return redirect()->route('transactions.payment', ['transactionId' => $this->transactionId]);
        }

        \DB::beginTransaction();
        
        try {
            foreach ($this->cart as $itemData) {
                $this->transaction->items()->create([
                    'product_id' => $itemData['id'],
                    'quantity' => $itemData['quantity'],
                    'price_per_item' => $itemData['price'],
                    'total_price' => $itemData['price'] * $itemData['quantity'],
                ]);
            }

            \DB::commit();

            // Clear the cart
            $this->cart = [];

            // Redirect to payment page
            return redirect()->route('transactions.payment', ['transactionId' => $this->transactionId]);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Failed to add items to transaction: ' . $e->getMessage());
        }
    }

    public function updatedSearch()
    {
        $this->loadProducts();
    }

    public function render()
    {
        return view('livewire.transactions.add-items', [
            'table' => $this->transaction?->table,
            'existingItems' => $this->transaction ? $this->transaction->items()->with('product')->get() : collect([])
        ]);
    }
}