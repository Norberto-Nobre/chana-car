<?php

namespace App\Repositories;

use App\Contracts\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function find(int $id): ?Category
    {
        return Category::with(['vehicles'])->find($id);
    }

    public function update(int $id, array $data): bool
    {
        return Category::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return Category::destroy($id);
    }

    public function getAll(): Collection
    {
        return Category::ordered()->get();
    }

    public function getActive(): Collection
    {
        return Category::active()->ordered()->get();
    }

    public function getWithVehicles(): Collection
    {
        return Category::with(['vehicles'])
            ->active()
            ->ordered()
            ->get();
    }

    public function getWithAvailableVehicles(): Collection
    {
        return Category::with(['availableVehicles'])
            ->active()
            ->ordered()
            ->get();
    }

    public function updateStatus(int $id, string $status): bool
    {
        return Category::where('id', $id)->update(['status' => $status]);
    }
}