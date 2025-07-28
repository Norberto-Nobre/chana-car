<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Contracts\CategoryRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService,
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function index(): JsonResponse
    {
        $categories = $this->categoryRepository->getAll();
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            $category = $this->categoryService->createCategory($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Categoria criada com sucesso',
                'data' => $category
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function show(int $id): JsonResponse
    {
        $category = $this->categoryRepository->find($id);
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Categoria não encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            $updated = $this->categoryService->updateCategory($id, $request->all());
            
            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoria não encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Categoria atualizada com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->categoryService->deleteCategory($id);
            
            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoria não encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Categoria excluída com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function active(): JsonResponse
    {
        $categories = $this->categoryService->getActiveCategories();
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function withVehicles(): JsonResponse
    {
        $categories = $this->categoryService->getCategoriesWithVehicles();
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function withAvailableVehicles(): JsonResponse
    {
        $categories = $this->categoryService->getCategoriesWithAvailableVehicles();
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}