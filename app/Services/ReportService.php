<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getBookingsByPeriod(Carbon $startDate, Carbon $endDate): array
    {
        return Booking::with(['user', 'vehicle.category'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function getMostRentedVehicles(): array
    {
        return Vehicle::select('vehicles.*', DB::raw('COUNT(bookings.id) as booking_count'))
            ->with(['category'])
            ->leftJoin('bookings', 'vehicles.id', '=', 'bookings.vehicle_id')
            ->groupBy('vehicles.id')
            ->orderBy('booking_count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function getMostRentedCategories(): array
    {
        return Category::select('categories.*', DB::raw('COUNT(bookings.id) as booking_count'))
            ->leftJoin('vehicles', 'categories.id', '=', 'vehicles.category_id')
            ->leftJoin('bookings', 'vehicles.id', '=', 'bookings.vehicle_id')
            ->groupBy('categories.id')
            ->orderBy('booking_count', 'desc')
            ->get()
            ->toArray();
    }

    public function getRevenueByCategory(): array
    {
        return Category::select('categories.*', DB::raw('SUM(bookings.total) as total_revenue'))
            ->leftJoin('vehicles', 'categories.id', '=', 'vehicles.category_id')
            ->leftJoin('bookings', 'vehicles.id', '=', 'bookings.vehicle_id')
            ->where('bookings.status', Booking::STATUS_RETURNED)
            ->groupBy('categories.id')
            ->orderBy('total_revenue', 'desc')
            ->get()
            ->toArray();
    }

    public function getMonthlyRevenue(): array
    {
        return Booking::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('COUNT(*) as total_bookings')
            )
            ->where('status', Booking::STATUS_RETURNED)
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->toArray();
    }

    public function getDashboardStats(): array
    {
        return [
            'total_categories' => Category::active()->count(),
            'total_vehicles' => Vehicle::count(),
            'available_vehicles' => Vehicle::where('status', Vehicle::STATUS_AVAILABLE)->count(),
            'active_bookings' => Booking::where('status', Booking::STATUS_APPROVED)->count(),
            'pending_bookings' => Booking::where('status', Booking::STATUS_PENDING)->count(),
            'monthly_revenue' => Booking::where('status', Booking::STATUS_RETURNED)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('total'),
        ];
    }
}