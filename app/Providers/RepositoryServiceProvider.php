<?php

namespace App\Providers;

use App\Contracts\BookingRepositoryInterface;
use App\Contracts\VehicleRepositoryInterface;
use App\Contracts\CategoryRepositoryInterface;
use App\Contracts\ContractServiceInterface;
use App\Repositories\BookingRepository;
use App\Repositories\VehicleRepository;
use App\Repositories\CategoryRepository;
use App\Services\ContractService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BookingRepositoryInterface::class, BookingRepository::class);
        $this->app->bind(VehicleRepositoryInterface::class, VehicleRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(ContractServiceInterface::class, ContractService::class);
    }

    public function boot(): void
    {
        //
    }
}