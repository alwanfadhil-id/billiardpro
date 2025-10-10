<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'price',
        'category',
        'stock_quantity',
        'min_stock_level',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'min_stock_level' => 'integer',
    ];

    /**
     * Boot the model and add global validation rules.
     */
    protected static function booted()
    {
        static::creating(function ($product) {
            $product->validatePrice();
        });

        static::updating(function ($product) {
            $product->validatePrice();
        });
    }

    /**
     * Validate the price attribute.
     */
    public function validatePrice()
    {
        if ($this->price <= 0) {
            throw new \InvalidArgumentException('Price must be greater than 0');
        }
    }

    /**
     * Check if the product is low on stock.
     */
    public function isLowStock()
    {
        return $this->stock_quantity <= $this->min_stock_level;
    }

    /**
     * Reduce stock quantity.
     */
    public function reduceStock($quantity)
    {
        if ($this->stock_quantity < $quantity) {
            throw new \InvalidArgumentException('Insufficient stock for product: ' . $this->name);
        }
        
        $this->update([
            'stock_quantity' => $this->stock_quantity - $quantity
        ]);
    }

    /**
     * Increase stock quantity.
     */
    public function increaseStock($quantity)
    {
        $this->update([
            'stock_quantity' => $this->stock_quantity + $quantity
        ]);
    }

    /**
     * Get the transaction items for the product.
     */
    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }
}