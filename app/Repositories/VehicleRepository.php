<?php

namespace App\Repositories;

use App\Contracts\VehicleRepositoryInterface;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

class VehicleRepository implements VehicleRepositoryInterface
{
    public function create(array $data): Vehicle
    {
        return Vehicle::create($data);
    }

    public function find(int $id): ?Vehicle
    {
        return Vehicle::with(['category', 'bookings', 'maintenances'])->find($id);
    }

    public function update(int $id, array $data): bool
    {
        return Vehicle::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return Vehicle::destroy($id);
    }

    public function getAll(): Collection
    {
        return Vehicle::with(['category'])
            ->orderBy('brand')
            ->orderBy('model')
            ->get();
    }

    public function getAvailable(): Collection
    {
        return Vehicle::with(['category'])
            ->available()
            ->orderBy('brand')
            ->orderBy('model')
            ->get();
    }

    public function getByType(string $type): Collection
    {
        return Vehicle::with(['category'])
            ->where('type', $type)
            ->orderBy('brand')
            ->orderBy('model')
            ->get();
    }

    public function getByStatus(string $status): Collection
    {
        return Vehicle::with(['category'])
            ->where('status', $status)
            ->orderBy('brand')
            ->orderBy('model')
            ->get();
    }

    public function getByCategory(int $categoryId): Collection
    {
        return Vehicle::with(['category'])
            ->byCategory($categoryId)
            ->orderBy('brand')
            ->orderBy('model')
            ->get();
    }

    public function getAvailableByCategory(int $categoryId): Collection
    {
        return Vehicle::with(['category'])
            ->byCategory($categoryId)
            ->available()
            ->orderBy('brand')
            ->orderBy('model')
            ->get();
    }

    public function updateStatus(int $id, string $status): bool
    {
        return Vehicle::where('id', $id)->update(['status' => $status]);
    }
}