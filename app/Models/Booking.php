<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_RETURNED = 'returned';
    const STATUS_EXPIRED = 'expired';

    const PAYMENT_MULTICAIXA = 'multicaixa';
    const PAYMENT_TRANSFER = 'transfer';
    const PAYMENT_CASH = 'cash';

    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_PENDING = 'pending';

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'booking_code',
        'start_date',
        'end_date',
        'pickup_date',
        'return_date',
        'total',
        'status',
        'payment_method',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'pickup_date' => 'datetime',
        'return_date' => 'datetime',
        'total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            $booking->booking_code = 'BK' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }

    public function getDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public function isOverdue(): bool
    {
        return $this->isApproved() && 
            !$this->pickup_date && 
            Carbon::now()->gt($this->start_date->addHours(24));
    }

    public function markAsExpired(): void
    {
        $this->update([
            'status' => self::STATUS_EXPIRED
        ]);
        
        $this->vehicle->update([
            'status' => Vehicle::STATUS_AVAILABLE
        ]);
    }
}
