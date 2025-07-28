<?php

namespace App\Repositories;

use App\Contracts\BookingRepositoryInterface;
use App\Models\Booking;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class BookingRepository implements BookingRepositoryInterface
{
    public function create(array $data): Booking
    {
        return Booking::create($data);
    }

    public function find(int $id): ?Booking
    {
        return Booking::with(['user', 'vehicle', 'contract'])->find($id);
    }

    public function findByCode(string $code): ?Booking
    {
        return Booking::with(['user', 'vehicle', 'contract'])
            ->where('booking_code', $code)
            ->first();
    }

    public function update(int $id, array $data): bool
    {
        return Booking::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return Booking::destroy($id);
    }

    public function getByUser(int $userId): Collection
    {
        return Booking::with(['vehicle'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByVehicle(int $vehicleId): Collection
    {
        return Booking::with(['user'])
            ->where('vehicle_id', $vehicleId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByStatus(string $status): Collection
    {
        return Booking::with(['user', 'vehicle'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getOverdueBookings(): Collection
    {
        return Booking::with(['user', 'vehicle'])
            ->where('status', Booking::STATUS_APPROVED)
            ->whereNull('pickup_date')
            ->where('start_date', '<', Carbon::now()->subDay())
            ->get();
    }

    public function isVehicleAvailable(int $vehicleId, Carbon $startDate, Carbon $endDate): bool
    {
        return !Booking::where('vehicle_id', $vehicleId)
            ->whereIn('status', [Booking::STATUS_APPROVED, Booking::STATUS_PENDING])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();
    }
}