<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;

class ProductList extends Component
{
    public $products;
    public $search = '';
    public $filterCategory = 'all';
    public $name;
    public $price;
    public $category = 'minuman';
    public $stock_quantity;
    public $min_stock_level = 5;
    public $editingProductId = null;
    public $showCreateForm = false;

    protected $rules = [
        'name' => 'required|string|max:100',
        'price' => 'required|numeric|min:0',
        'category' => 'required|string|max:50',
        'stock_quantity' => 'required|integer|min:0',
        'min_stock_level' => 'required|integer|min:0',
    ];

    public function mount()
    {
        $this->loadProducts();
    }

    public function render()
    {
        return view('livewire.products.product-list');
    }

    private function loadProducts()
    {
        $query = Product::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . trim($this->search) . '%');
        }

        if ($this->filterCategory !== 'all') {
            $query->where('category', $this->filterCategory);
        }

        $this->products = $query->orderBy('name')->get();
    }
    
    public function clearSearch()
    {
        $this->search = '';
        $this->filterCategory = 'all';
        $this->loadProducts();
    }

    public function updatedSearch()
    {
        $this->loadProducts();
    }

    public function updatedFilterCategory()
    {
        $this->loadProducts();
    }

    public function create()
    {
        $this->resetForm();
        $this->showCreateForm = true;
    }

    public function store()
    {
        $this->validate();

        Product::create([
            'name' => $this->name,
            'price' => $this->price,
            'category' => $this->category,
            'stock_quantity' => $this->stock_quantity,
            'min_stock_level' => $this->min_stock_level,
        ]);

        $this->showCreateForm = false;
        $this->loadProducts();
        $this->resetForm();
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $this->editingProductId = $id;
        $this->name = $product->name;
        $this->price = $product->price;
        $this->category = $product->category;
        $this->stock_quantity = $product->stock_quantity;
        $this->min_stock_level = $product->min_stock_level;
    }

    public function update()
    {
        $this->validate();

        $product = Product::findOrFail($this->editingProductId);
        $product->update([
            'name' => $this->name,
            'price' => $this->price,
            'category' => $this->category,
            'stock_quantity' => $this->stock_quantity,
            'min_stock_level' => $this->min_stock_level,
        ]);

        $this->resetForm();
        $this->loadProducts();
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        $this->loadProducts();
    }

    public function cancel()
    {
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->name = '';
        $this->price = '';
        $this->category = 'minuman';
        $this->stock_quantity = '';
        $this->min_stock_level = 5;
        $this->editingProductId = null;
        $this->showCreateForm = false;
    }
}