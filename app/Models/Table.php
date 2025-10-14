<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'hourly_rate',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'status' => 'string',
    ];

    /**
     * Boot the model and add global validation rules.
     */
    protected static function booted()
    {
        static::creating(function ($table) {
            $table->validateRate();
            $table->validateType();
            $table->validateStatus();
        });

        static::updating(function ($table) {
            $table->validateRate();
            $table->validateType();
            $table->validateStatus();
        });
    }

    /**
     * Validate the hourly rate attribute.
     */
    public function validateRate()
    {
        if ($this->hourly_rate <= 0) {
            throw new \InvalidArgumentException('Hourly rate must be greater than 0');
        }
    }

    /**
     * Validate the type attribute.
     */
    public function validateType()
    {
        $validTypes = ['biasa', 'premium', 'vip'];
        if (!in_array($this->type, $validTypes)) {
            throw new \InvalidArgumentException("Type must be one of: " . implode(', ', $validTypes));
        }
    }

    /**
     * Validate the status attribute.
     */
    public function validateStatus()
    {
        $validStatuses = ['available', 'occupied', 'maintenance'];
        if (!in_array($this->status, $validStatuses)) {
            throw new \InvalidArgumentException("Status must be one of: " . implode(', ', $validStatuses));
        }
    }

    /**
     * Check if table is available.
     */
    public function isAvailable()
    {
        return $this->status === 'available';
    }

    /**
     * Check if table is occupied.
     */
    public function isOccupied()
    {
        return $this->status === 'occupied';
    }

    /**
     * Check if table is under maintenance.
     */
    public function isMaintenance()
    {
        return $this->status === 'maintenance';
    }

    /**
     * Get the transactions for the table.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}