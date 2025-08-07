<?php

namespace App\Http\Controllers;

use App\Contracts\VehicleRepositoryInterface;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VehicleController extends Controller
{
    public function __construct(
        private VehicleRepositoryInterface $vehicleRepository
    ) {}

    // ✅ Agora retorna uma view Blade ao invés de JSON
    public function index()
    {
        // $vehicles = $this->vehicleRepository->getAll();
        $vehicles = $this->vehicleRepository->getAll()->load('category');
        $categories = Category::with('vehicles')->get();

        // Retorna a view resources/views/vehicles/index.blade.php
        return view('pages.index', compact('vehicles', 'categories'));
    }

    public function carros()
    {
        $vehicles = $this->vehicleRepository->getAll()->load('category');
         $categories = Category::with('vehicles')->get();

        // Retorna a view resources/views/vehicles/carros.blade.php
        return view('pages.carros', compact('vehicles', 'categories'));
    }

    public function carroDetalhe(){
         $vehicles = $this->vehicleRepository->getAll()->load('category');
         $categories = Category::with('vehicles')->get();

        // Retorna a view resources/views/vehicles/carros.blade.php
        return view('pages.carro-detalhe', compact('vehicles', 'categories'));
    }

    // Continuação dos métodos da API (inalterados)
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'plate' => 'required|string|max:20|unique:vehicles,plate',
            'km' => 'nullable|integer|min:0',
            'type' => 'required|in:SUV,Sedan,Pick-Up,Hatchback,Convertible',
            'price_per_day' => 'required|numeric|min:0',
            'color' => 'required|string|max:50',
            'doors' => 'required|integer|min:2|max:8',
            'fuel' => 'required|in:gasoline,diesel,electric,hybrid',
            'images' => 'nullable|array',
        ]);

        try {
            $vehicle = $this->vehicleRepository->create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Veículo criado com sucesso',
                'data' => $vehicle
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
        $vehicle = $this->vehicleRepository->find($id);

        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Veículo não encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $vehicle
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'plate' => 'required|string|max:20|unique:vehicles,plate,' . $id,
            'km' => 'nullable|integer|min:0',
            'type' => 'required|in:SUV,Sedan,Pick-Up,Hatchback,Convertible',
            'price_per_day' => 'required|numeric|min:0',
            'color' => 'required|string|max:50',
            'doors' => 'required|integer|min:2|max:8',
            'fuel' => 'required|in:gasoline,diesel,electric,hybrid',
            'images' => 'nullable|array',
        ]);

        try {
            $updated = $this->vehicleRepository->update($id, $request->all());

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Veículo não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Veículo atualizado com sucesso'
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
            $deleted = $this->vehicleRepository->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Veículo não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Veículo excluído com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function available(): JsonResponse
    {
        $vehicles = $this->vehicleRepository->getAvailable();

        return response()->json([
            'success' => true,
            'data' => $vehicles
        ]);
    }

    public function byCategory(int $categoryId): JsonResponse
    {
        $vehicles = $this->vehicleRepository->getByCategory($categoryId);

        return response()->json([
            'success' => true,
            'data' => $vehicles
        ]);
    }

    public function availableByCategory(int $categoryId): JsonResponse
    {
        $vehicles = $this->vehicleRepository->getAvailableByCategory($categoryId);

        return response()->json([
            'success' => true,
            'data' => $vehicles
        ]);
    }
}
