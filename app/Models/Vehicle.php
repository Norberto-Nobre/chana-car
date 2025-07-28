<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory;

    const STATUS_AVAILABLE = 'available';
    const STATUS_IN_USE = 'in_use';
    const STATUS_MAINTENANCE = 'maintenance';

    const TYPE_SUV = 'SUV'; // Utilitário Esportivo
    const TYPE_SEDAN = 'Sedan'; // Sedan Ex. Toyota Corolla
    const TYPE_PICKUP = 'Pick-Up'; // Carrinha / Caminhonete
    const TYPE_HATCHBACK = 'Hatchback'; // Compacto com Porta Traseira
    const TYPE_CONVERTIBLE = 'Convertible'; // Conversível

    const FUEL_GASOLINE = 'gasoline';
    const FUEL_DIESEL = 'diesel';
    const FUEL_ELECTRIC = 'electric';
    const FUEL_HYBRID = 'hybrid';

    protected $fillable = [
        'category_id',
        'brand',
        'model',
        'year',
        'plate',
        'km',
        'type',
        'status',
        'price_per_day',
        'color',
        'doors',
        'fuel',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
        'price_per_day' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isInUse(): bool
    {
        return $this->status === self::STATUS_IN_USE;
    }

    public function isInMaintenance(): bool
    {
        return $this->status === self::STATUS_MAINTENANCE;
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->brand} {$this->model} ({$this->year})";
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}
