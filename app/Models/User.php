<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => 'string',
    ];

    /**
     * Boot the model and add global validation rules.
     */
    protected static function booted()
    {
        static::creating(function ($user) {
            $user->validateRole();
        });

        static::updating(function ($user) {
            $user->validateRole();
        });
    }

    /**
     * Validate the role attribute.
     */
    public function validateRole()
    {
        $validRoles = ['admin', 'cashier'];
        if (!in_array($this->role, $validRoles)) {
            throw new \InvalidArgumentException("Role must be either 'admin' or 'cashier'");
        }
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is cashier.
     */
    public function isCashier()
    {
        return $this->role === 'cashier';
    }

    /**
     * Get the transactions for the user.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}