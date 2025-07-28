<?php

namespace App\Contracts;

use App\Models\Booking;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface BookingRepositoryInterface
{
    public function create(array $data): Booking;
    public function find(int $id): ?Booking;
    public function findByCode(string $code): ?Booking;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getByUser(int $userId): Collection;
    public function getByVehicle(int $vehicleId): Collection;
    public function getByStatus(string $status): Collection;
    public function getOverdueBookings(): Collection;
    public function isVehicleAvailable(int $vehicleId, Carbon $startDate, Carbon $endDate): bool;
}