<?php

namespace App\Livewire\Products;

use Livewire\Component;
use App\Models\Product;
use Livewire\WithPagination;

class ProductForm extends Component
{
    use WithPagination;

    public $productId;
    public $name;
    public $price;
    public $category;
    public $stock_quantity = 0;
    public $min_stock_level = 5;

    protected $rules = [
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'category' => 'nullable|string|max:100',
        'stock_quantity' => 'required|integer|min:0',
        'min_stock_level' => 'required|integer|min:0',
    ];

    public function render()
    {
        $products = Product::paginate(10);
        return view('livewire.products.product-form', [
            'products' => $products
        ]);
    }

    public function save()
    {
        $this->validate();

        if ($this->productId) {
            $product = Product::findOrFail($this->productId);
            $product->update([
                'name' => $this->name,
                'price' => $this->price,
                'category' => $this->category,
                'stock_quantity' => $this->stock_quantity,
                'min_stock_level' => $this->min_stock_level,
            ]);
        } else {
            Product::create([
                'name' => $this->name,
                'price' => $this->price,
                'category' => $this->category,
                'stock_quantity' => $this->stock_quantity,
                'min_stock_level' => $this->min_stock_level,
            ]);
        }

        session()->flash('message', $this->productId ? 'Product updated successfully.' : 'Product created successfully.');
        $this->resetForm();
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $this->productId = $product->id;
        $this->name = $product->name;
        $this->price = $product->price;
        $this->category = $product->category;
        $this->stock_quantity = $product->stock_quantity;
        $this->min_stock_level = $product->min_stock_level;
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        session()->flash('message', 'Product deleted successfully.');
    }

    public function cancel()
    {
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->productId = null;
        $this->name = '';
        $this->price = '';
        $this->category = '';
        $this->stock_quantity = 0;
        $this->min_stock_level = 5;
    }
}