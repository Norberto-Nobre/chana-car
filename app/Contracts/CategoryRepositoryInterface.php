<?php

namespace App\Contracts;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    public function create(array $data): Category;
    public function find(int $id): ?Category;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getAll(): Collection;
    public function getActive(): Collection;
    public function getWithVehicles(): Collection;
    public function getWithAvailableVehicles(): Collection;
    public function updateStatus(int $id, string $status): bool;
}