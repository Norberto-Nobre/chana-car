<?php

namespace App\Contracts;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

interface VehicleRepositoryInterface
{
    public function create(array $data): Vehicle;
    public function find(int $id): ?Vehicle;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getAll(): Collection;
    public function getAvailable(): Collection;
    public function getByType(string $type): Collection;
    public function getByStatus(string $status): Collection;
    public function getByCategory(int $categoryId): Collection;
    public function getAvailableByCategory(int $categoryId): Collection;
    public function updateStatus(int $id, string $status): bool;
}