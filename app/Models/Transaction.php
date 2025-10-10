<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\InventoryTransaction;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'table_id',
        'user_id',
        'started_at',
        'ended_at',
        'duration_minutes',
        'total',
        'payment_method',
        'cash_received',
        'change_amount',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_minutes' => 'integer',
        'total' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'status' => 'string',
    ];

    /**
     * Boot the model and add global validation rules.
     */
    protected static function booted()
    {
        static::creating(function ($transaction) {
            $transaction->validatePaymentMethod();
            $transaction->validateStatus();
        });

        static::updating(function ($transaction) {
            $transaction->validatePaymentMethod();
            $transaction->validateStatus();
            
            // If the status is changing to completed and wasn't completed before
            if ($transaction->isDirty('status') && $transaction->status === 'completed' && $transaction->getOriginal('status') !== 'completed') {
                $transaction->handleStockReduction();
            }
        });
    }

    /**
     * Validate the payment method attribute.
     */
    public function validatePaymentMethod()
    {
        $validPaymentMethods = ['cash', 'qris', 'debit', 'credit', 'other'];
        if (!in_array($this->payment_method, $validPaymentMethods)) {
            throw new \InvalidArgumentException("Payment method must be one of: " . implode(', ', $validPaymentMethods));
        }
    }

    /**
     * Validate the status attribute.
     */
    public function validateStatus()
    {
        $validStatuses = ['ongoing', 'completed', 'cancelled'];
        if (!in_array($this->status, $validStatuses)) {
            throw new \InvalidArgumentException("Status must be one of: " . implode(', ', $validStatuses));
        }
    }

    /**
     * Check if transaction is ongoing.
     */
    public function isOngoing()
    {
        return $this->status === 'ongoing';
    }

    /**
     * Check if transaction is completed.
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if transaction is cancelled.
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Calculate the total amount for the transaction.
     * Formula: (duration rounded up to nearest hour × hourly_rate) + total item cost
     */
    public function calculateTotal()
    {
        // Get the table to get the hourly rate - use the relationship method directly
        $table = $this->table()->first();
        if (!$table) {
            throw new \Exception('Table not found for this transaction');
        }

        // Calculate duration in minutes
        $start = $this->started_at;
        $end = $this->ended_at ?? now();
        $durationMinutes = $start->diffInMinutes($end);

        // Round up to nearest hour
        $hours = ceil($durationMinutes / 60);

        // Calculate table cost
        $tableCost = $table->hourly_rate * $hours;

        // Calculate items cost using the relationship
        $items = $this->items;
        $itemsCost = $items->sum('total_price');

        return $tableCost + $itemsCost;
    }

    /**
     * Get the table for the transaction.
     */
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Get the user for the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the transaction.
     */
    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Handle stock reduction for items in this transaction.
     */
    public function handleStockReduction()
    {
        // Reduce stock for each item in the transaction
        foreach ($this->items as $item) {
            // Reduce the product stock
            $item->product->reduceStock($item->quantity);
            
            // Create an inventory transaction record
            InventoryTransaction::create([
                "product_id" => $item->product_id,
                "user_id" => $this->user_id,
                "type" => "out",
                "quantity" => $item->quantity,
                "description" => "Sold in transaction #" . $this->id,
            ]);
        }
    }
}
