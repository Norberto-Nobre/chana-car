<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const TYPE_CLIENT = 'client';
    const TYPE_EMPLOYEE = 'employee';
    const TYPE_MANAGER = 'manager';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'type',
        'status',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function isClient(): bool
    {
        return $this->type === self::TYPE_CLIENT;
    }

    public function isEmployee(): bool
    {
        return $this->type === self::TYPE_EMPLOYEE;
    }

    public function isManager(): bool
    {
        return $this->type === self::TYPE_MANAGER;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
