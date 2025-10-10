<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',
        'price_per_item',
        'total_price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'price_per_item' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Boot the model and add global validation rules.
     */
    protected static function booted()
    {
        static::creating(function ($transactionItem) {
            $transactionItem->validateQuantity();
            $transactionItem->validatePrices();
        });

        static::updating(function ($transactionItem) {
            $transactionItem->validateQuantity();
            $transactionItem->validatePrices();
        });
    }

    /**
     * Validate the quantity attribute.
     */
    public function validateQuantity()
    {
        if ($this->quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than 0');
        }
    }

    /**
     * Validate that the prices are positive.
     */
    public function validatePrices()
    {
        if ($this->price_per_item <= 0) {
            throw new \InvalidArgumentException('Price per item must be greater than 0');
        }
        
        if ($this->total_price <= 0) {
            throw new \InvalidArgumentException('Total price must be greater than 0');
        }
    }

    /**
     * Get the transaction for this item.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the product for this item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}