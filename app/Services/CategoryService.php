<?php

namespace App\Services;

use App\Contracts\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function createCategory(array $data): Category
    {
        DB::beginTransaction();
        
        try {
            // Se não foi fornecido sort_order, pegar o próximo disponível
            if (!isset($data['sort_order'])) {
                $data['sort_order'] = $this->getNextSortOrder();
            }

            $category = $this->categoryRepository->create($data);
            
            DB::commit();
            return $category;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateCategory(int $id, array $data): bool
    {
        DB::beginTransaction();
        
        try {
            $result = $this->categoryRepository->update($id, $data);
            
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteCategory(int $id): bool
    {
        DB::beginTransaction();
        
        try {
            $category = $this->categoryRepository->find($id);
            
            if (!$category) {
                throw new \Exception('Categoria não encontrada');
            }

            // Verificar se há veículos associados
            if ($category->vehicles()->count() > 0) {
                throw new \Exception('Não é possível excluir categoria com veículos associados');
            }

            $result = $this->categoryRepository->delete($id);
            
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getActiveCategories(): Collection
    {
        return $this->categoryRepository->getActive();
    }

    public function getCategoriesWithVehicles(): Collection
    {
        return $this->categoryRepository->getWithVehicles();
    }

    public function getCategoriesWithAvailableVehicles(): Collection
    {
        return $this->categoryRepository->getWithAvailableVehicles();
    }

    public function activateCategory(int $id): bool
    {
        return $this->categoryRepository->updateStatus($id, Category::STATUS_ACTIVE);
    }

    public function deactivateCategory(int $id): bool
    {
        return $this->categoryRepository->updateStatus($id, Category::STATUS_INACTIVE);
    }

    private function getNextSortOrder(): int
    {
        $lastCategory = Category::orderBy('sort_order', 'desc')->first();
        return $lastCategory ? $lastCategory->sort_order + 1 : 1;
    }
}