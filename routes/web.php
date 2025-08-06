<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return view('index');
});

// Rotas públicas para visualização de categorias e veículos
Route::prefix('api')->group(function () {
    Route::get('categories/active', [CategoryController::class, 'active']);
    Route::get('categories/with-available-vehicles', [CategoryController::class, 'withAvailableVehicles']);
    Route::get('vehicles/available', [VehicleController::class, 'available']);
    Route::get('vehicles/category/{categoryId}', [VehicleController::class, 'byCategory']);
    Route::get('vehicles/available/category/{categoryId}', [VehicleController::class, 'availableByCategory']);
});

// Rotas protegidas por autenticação
Route::middleware(['auth'])->group(function () {
    // Rotas para clientes
    Route::middleware(['role:client'])->prefix('client')->group(function () {
        Route::post('bookings', [BookingController::class, 'store']);
        Route::get('bookings', [BookingController::class, 'myBookings']);
        Route::get('bookings/{id}', [BookingController::class, 'show']);
    });

    // Rotas para funcionários e gerentes
    // Route::middleware(['role:employee,manager'])->prefix('admin')->group(function () {
    //     // Categorias
    //     Route::apiResource('categories', CategoryController::class);
    //     Route::get('categories/with-vehicles', [CategoryController::class, 'withVehicles']);

    //     // Veículos
    //     Route::apiResource('vehicles', VehicleController::class);

    //     // Reservas
    //     Route::apiResource('bookings', BookingController::class);
    //     Route::patch('bookings/{id}/approve', [BookingController::class, 'approve']);
    //     Route::patch('bookings/{id}/reject', [BookingController::class, 'reject']);
    //     Route::patch('bookings/{id}/return', [BookingController::class, 'return']);

    //     // Relatórios
    //     Route::get('reports/dashboard', [ReportController::class, 'dashboard']);
    //     Route::get('reports/bookings', [ReportController::class, 'bookings']);
    //     Route::get('reports/vehicles', [ReportController::class, 'vehicles']);
    //     Route::get('reports/categories', [ReportController::class, 'categories']);
    //     Route::get('reports/revenue', [ReportController::class, 'revenue']);
    // });

    // Rotas apenas para gerentes
    Route::middleware(['role:manager'])->prefix('manager')->group(function () {
        Route::get('reports/financial', [ReportController::class, 'financial']);
        Route::get('reports/advanced', [ReportController::class, 'advanced']);
    });
});
