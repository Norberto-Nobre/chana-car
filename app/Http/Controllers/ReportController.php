<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Dashboard statistics.
     */
    public function dashboard(Request $request)
    {
        $period = $request->get('period', 'month'); // day, week, month, year
        $startDate = $this->getStartDate($period);
        $endDate = Carbon::now();

        $stats = [
            'summary' => $this->getSummaryStats(),
            'bookings' => $this->getBookingStats($startDate, $endDate),
            'revenue' => $this->getRevenueStats($startDate, $endDate),
            'vehicles' => $this->getVehicleStats(),
            'recent_bookings' => $this->getRecentBookings(10),
            'top_categories' => $this->getTopCategories($startDate, $endDate),
            'monthly_trend' => $this->getMonthlyTrend()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'period' => $period,
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ]
        ]);
    }

    /**
     * Bookings report.
     */
    public function bookings(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $status = $request->get('status', 'all');
        $groupBy = $request->get('group_by', 'day'); // day, week, month

        $query = Booking::with(['vehicle.category', 'user'])
                       ->whereBetween('created_at', [$startDate, $endDate]);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->get();

        // Estatísticas por status
        $statusStats = [
            'total' => $bookings->count(),
            'pending' => $bookings->where('status', 'pending')->count(),
            'approved' => $bookings->where('status', 'approved')->count(),
            'active' => $bookings->where('status', 'active')->count(),
            'completed' => $bookings->where('status', 'completed')->count(),
            'cancelled' => $bookings->where('status', 'cancelled')->count(),
        ];

        // Dados agrupados por período
        $groupedData = $this->groupBookingsByPeriod($bookings, $groupBy);

        // Top veículos mais reservados
        $topVehicles = $bookings->groupBy('vehicle_id')
                               ->map(function ($group) {
                                   $vehicle = $group->first()->vehicle;
                                   return [
                                       'vehicle' => $vehicle,
                                       'bookings_count' => $group->count(),
                                       'total_revenue' => $group->sum('total_amount')
                                   ];
                               })
                               ->sortByDesc('bookings_count')
                               ->take(10)
                               ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'bookings' => $bookings,
                'status_stats' => $statusStats,
                'grouped_data' => $groupedData,
                'top_vehicles' => $topVehicles,
                'filters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => $status,
                    'group_by' => $groupBy
                ]
            ]
        ]);
    }

    /**
     * Vehicles report.
     */
    public function vehicles(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $vehicles = Vehicle::with(['category', 'bookings' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                  ->whereIn('status', ['completed', 'active']);
        }])->get();

        $totalDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;

        $vehicleStats = $vehicles->map(function ($vehicle) use ($totalDays) {
            $bookings = $vehicle->bookings;
            $bookedDays = $bookings->sum(function ($booking) {
                return Carbon::parse($booking->start_date)->diffInDays(Carbon::parse($booking->end_date)) + 1;
            });

            $utilizationRate = $totalDays > 0 ? ($bookedDays / $totalDays) * 100 : 0;

            return [
                'vehicle' => $vehicle,
                'bookings_count' => $bookings->count(),
                'booked_days' => $bookedDays,
                'total_days' => $totalDays,
                'utilization_rate' => round($utilizationRate, 2),
                'total_revenue' => $bookings->sum('total_amount'),
                'avg_daily_rate' => $vehicle->daily_rate,
                'efficiency' => $bookings->count() > 0 ? 
                    round($bookings->sum('total_amount') / $bookings->count(), 2) : 0
            ];
        })->sortByDesc('utilization_rate');

        // Estatísticas gerais
        $overallStats = [
            'total_vehicles' => $vehicles->count(),
            'active_vehicles' => $vehicles->where('status', 'available')->count(),
            'avg_utilization' => $vehicleStats->avg('utilization_rate'),
            'total_revenue' => $vehicleStats->sum('total_revenue'),
            'most_utilized' => $vehicleStats->first(),
            'least_utilized' => $vehicleStats->last()
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'vehicle_stats' => $vehicleStats->values(),
                'overall_stats' => $overallStats,
                'filters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'total_days' => $totalDays
                ]
            ]
        ]);
    }

    /**
     * Categories report.
     */
    public function categories(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $categories = Category::with(['vehicles.bookings' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                  ->whereIn('status', ['completed', 'active']);
        }])->get();

        $categoryStats = $categories->map(function ($category) {
            $allBookings = collect();
            
            foreach ($category->vehicles as $vehicle) {
                $allBookings = $allBookings->merge($vehicle->bookings);
            }

            return [
                'category' => $category,
                'vehicles_count' => $category->vehicles->count(),
                'total_bookings' => $allBookings->count(),
                'total_revenue' => $allBookings->sum('total_amount'),
                'avg_booking_value' => $allBookings->count() > 0 ? 
                    round($allBookings->avg('total_amount'), 2) : 0,
                'utilization_rate' => $this->calculateCategoryUtilization($category, $allBookings)
            ];
        })->sortByDesc('total_revenue');

        return response()->json([
            'success' => true,
            'data' => [
                'category_stats' => $categoryStats->values(),
                'filters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]
        ]);
    }

    /**
     * Revenue report.
     */
    public function revenue(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $groupBy = $request->get('group_by', 'day'); // day, week, month

        // Receita por período
        $revenueData = $this->getRevenueByPeriod($startDate, $endDate, $groupBy);
        
        // Receita por categoria
        $revenueByCategory = $this->getRevenueByCategory($startDate, $endDate);
        
        // Receita por veículo
        $revenueByVehicle = $this->getRevenueByVehicle($startDate, $endDate, 10);

        // Estatísticas gerais
        $totalRevenue = Booking::whereBetween('created_at', [$startDate, $endDate])
                              ->whereIn('status', ['completed', 'active'])
                              ->sum('total_amount');

        $additionalCharges = Booking::whereBetween('created_at', [$startDate, $endDate])
                                   ->whereIn('status', ['completed'])
                                   ->sum('additional_charges');

        $avgBookingValue = Booking::whereBetween('created_at', [$startDate, $endDate])
                                 ->whereIn('status', ['completed', 'active'])
                                 ->avg('total_amount');

        return response()->json([
            'success' => true,
            'data' => [
                'revenue_by_period' => $revenueData,
                'revenue_by_category' => $revenueByCategory,
                'revenue_by_vehicle' => $revenueByVehicle,
                'summary' => [
                    'total_revenue' => round($totalRevenue, 2),
                    'additional_charges' => round($additionalCharges, 2),
                    'net_revenue' => round($totalRevenue + $additionalCharges, 2),
                    'avg_booking_value' => round($avgBookingValue, 2)
                ],
                'filters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'group_by' => $groupBy
                ]
            ]
        ]);
    }

    /**
     * Financial report (Manager only).
     */
    public function financial(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $financialData = [
            'income' => [
                'gross_revenue' => $this->getGrossRevenue($startDate, $endDate),
                'additional_charges' => $this->getAdditionalCharges($startDate, $endDate),
                'total_income' => 0 // Será calculado
            ],
            'bookings_analysis' => [
                'total_bookings' => $this->getTotalBookings($startDate, $endDate),
                'completed_bookings' => $this->getCompletedBookings($startDate, $endDate),
                'cancelled_bookings' => $this->getCancelledBookings($startDate, $endDate),
                'completion_rate' => 0 // Será calculado
            ],
            'payment_methods' => $this->getPaymentMethodsBreakdown($startDate, $endDate),
            'monthly_comparison' => $this->getMonthlyComparison(),
            'profit_margins' => $this->getProfitMargins($startDate, $endDate)
        ];

        // Cálculos derivados
        $financialData['income']['total_income'] = 
            $financialData['income']['gross_revenue'] + $financialData['income']['additional_charges'];

        $totalBookings = $financialData['bookings_analysis']['total_bookings'];
        $completedBookings = $financialData['bookings_analysis']['completed_bookings'];
        $financialData['bookings_analysis']['completion_rate'] = 
            $totalBookings > 0 ? round(($completedBookings / $totalBookings) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'data' => $financialData,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }

    /**
     * Advanced analytics (Manager only).
     */
    public function advanced(Request $request)
    {
        $period = $request->get('period', 'year'); // month, quarter, year

        $advancedData = [
            'customer_analytics' => $this->getCustomerAnalytics($period),
            'seasonal_trends' => $this->getSeasonalTrends(),
            'vehicle_performance' => $this->getVehiclePerformanceMetrics(),
            'predictive_analytics' => $this->getPredictiveAnalytics(),
            'kpi_metrics' => $this->getKPIMetrics($period)
        ];

        return response()->json([
            'success' => true,
            'data' => $advancedData,
            'period' => $period,
            'generated_at' => now()->toISOString()
        ]);
    }

    // Helper Methods

    private function getStartDate($period)
    {
        return match ($period) {
            'day' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth()
        };
    }

    private function getSummaryStats()
    {
        return [
            'total_bookings' => Booking::count(),
            'active_bookings' => Booking::where('status', 'active')->count(),
            'total_vehicles' => Vehicle::count(),
            'available_vehicles' => Vehicle::where('status', 'available')->count(),
            'total_customers' => User::where('role', 'client')->count(),
            'total_revenue' => Booking::whereIn('status', ['completed', 'active'])->sum('total_amount')
        ];
    }

    private function getBookingStats($startDate, $endDate)
    {
        return [
            'total' => Booking::whereBetween('created_at', [$startDate, $endDate])->count(),
            'completed' => Booking::whereBetween('created_at', [$startDate, $endDate])
                                 ->where('status', 'completed')->count(),
            'active' => Booking::whereBetween('created_at', [$startDate, $endDate])
                              ->where('status', 'active')->count(),
            'cancelled' => Booking::whereBetween('created_at', [$startDate, $endDate])
                                 ->where('status', 'cancelled')->count()
        ];
    }

    private function getRevenueStats($startDate, $endDate)
    {
        return [
            'total' => Booking::whereBetween('created_at', [$startDate, $endDate])
                             ->whereIn('status', ['completed', 'active'])
                             ->sum('total_amount'),
            'average_booking' => Booking::whereBetween('created_at', [$startDate, $endDate])
                                       ->whereIn('status', ['completed', 'active'])
                                       ->avg('total_amount') ?? 0
        ];
    }

    private function getVehicleStats()
    {
        return [
            'total' => Vehicle::count(),
            'available' => Vehicle::where('status', 'available')->count(),
            'booked' => Vehicle::where('status', 'booked')->count(),
            'maintenance' => Vehicle::where('status', 'maintenance')->count()
        ];
    }

    private function getRecentBookings($limit)
    {
        return Booking::with(['vehicle.category', 'user'])
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    private function getTopCategories($startDate, $endDate)
    {
        return DB::table('bookings')
                 ->join('vehicles', 'bookings.vehicle_id', '=', 'vehicles.id')
                 ->join('categories', 'vehicles.category_id', '=', 'categories.id')
                 ->whereBetween('bookings.created_at', [$startDate, $endDate])
                 ->whereIn('bookings.status', ['completed', 'active'])
                 ->select(
                     'categories.name',
                     'categories.id',
                     DB::raw('COUNT(bookings.id) as total_bookings'),
                     DB::raw('SUM(bookings.total_amount) as total_revenue')
                 )
                 ->groupBy('categories.id', 'categories.name')
                 ->orderByDesc('total_revenue')
                 ->limit(5)
                 ->get();
    }

    private function getMonthlyTrend()
    {
        return DB::table('bookings')
                 ->whereIn('status', ['completed', 'active'])
                 ->where('created_at', '>=', Carbon::now()->subMonths(12))
                 ->select(
                     DB::raw('YEAR(created_at) as year'),
                     DB::raw('MONTH(created_at) as month'),
                     DB::raw('COUNT(*) as bookings_count'),
                     DB::raw('SUM(total_amount) as revenue')
                 )
                 ->groupBy('year', 'month')
                 ->orderBy('year')
                 ->orderBy('month')
                 ->get();
    }

    private function groupBookingsByPeriod($bookings, $groupBy)
    {
        $format = match ($groupBy) {
            'week' => 'Y-W',
            'month' => 'Y-m',
            default => 'Y-m-d'
        };

        return $bookings->groupBy(function ($booking) use ($format) {
            return Carbon::parse($booking->created_at)->format($format);
        })->map(function ($group, $period) {
            return [
                'period' => $period,
                'count' => $group->count(),
                'revenue' => $group->sum('total_amount')
            ];
        })->values();
    }

    private function calculateCategoryUtilization($category, $bookings)
    {
        if ($category->vehicles->count() === 0) {
            return 0;
        }

        $totalPossibleDays = $category->vehicles->count() * 30; // Assumindo 30 dias no período
        $actualBookedDays = $bookings->sum(function ($booking) {
            return Carbon::parse($booking->start_date)->diffInDays(Carbon::parse($booking->end_date)) + 1;
        });

        return $totalPossibleDays > 0 ? round(($actualBookedDays / $totalPossibleDays) * 100, 2) : 0;
    }

    private function getRevenueByPeriod($startDate, $endDate, $groupBy)
    {
        $dateFormat = match ($groupBy) {
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        return DB::table('bookings')
                 ->whereBetween('created_at', [$startDate, $endDate])
                 ->whereIn('status', ['completed', 'active'])
                 ->select(
                     DB::raw("DATE_FORMAT(created_at, '$dateFormat') as period"),
                     DB::raw('SUM(total_amount + COALESCE(additional_charges, 0)) as revenue'),
                     DB::raw('COUNT(*) as bookings_count')
                 )
                 ->groupBy('period')
                 ->orderBy('period')
                 ->get();
    }

    private function getRevenueByCategory($startDate, $endDate)
    {
        return DB::table('bookings')
                 ->join('vehicles', 'bookings.vehicle_id', '=', 'vehicles.id')
                 ->join('categories', 'vehicles.category_id', '=', 'categories.id')
                 ->whereBetween('bookings.created_at', [$startDate, $endDate])
                 ->whereIn('bookings.status', ['completed', 'active'])
                 ->select(
                     'categories.name as category_name',
                     DB::raw('SUM(bookings.total_amount) as revenue'),
                     DB::raw('COUNT(bookings.id) as bookings_count')
                 )
                 ->groupBy('categories.id', 'categories.name')
                 ->orderByDesc('revenue')
                 ->get();
    }

    private function getRevenueByVehicle($startDate, $endDate, $limit)
    {
        return DB::table('bookings')
                 ->join('vehicles', 'bookings.vehicle_id', '=', 'vehicles.id')
                 ->join('categories', 'vehicles.category_id', '=', 'categories.id')
                 ->whereBetween('bookings.created_at', [$startDate, $endDate])
                 ->whereIn('bookings.status', ['completed', 'active'])
                 ->select(
                     'vehicles.brand',
                     'vehicles.model',
                     'vehicles.license_plate',
                     'categories.name as category_name',
                     DB::raw('SUM(bookings.total_amount) as revenue'),
                     DB::raw('COUNT(bookings.id) as bookings_count')
                 )
                 ->groupBy('vehicles.id', 'vehicles.brand', 'vehicles.model', 'vehicles.license_plate', 'categories.name')
                 ->orderByDesc('revenue')
                 ->limit($limit)
                 ->get();
    }

    private function getGrossRevenue($startDate, $endDate)
    {
        return Booking::whereBetween('created_at', [$startDate, $endDate])
                     ->whereIn('status', ['completed', 'active'])
                     ->sum('total_amount');
    }

    private function getAdditionalCharges($startDate, $endDate)
    {
        return Booking::whereBetween('created_at', [$startDate, $endDate])
                     ->where('status', 'completed')
                     ->sum('additional_charges');
    }

    private function getTotalBookings($startDate, $endDate)
    {
        return Booking::whereBetween('created_at', [$startDate, $endDate])->count();
    }

    private function getCompletedBookings($startDate, $endDate)
    {
        return Booking::whereBetween('created_at', [$startDate, $endDate])
                     ->where('status', 'completed')
                     ->count();
    }

    private function getCancelledBookings($startDate, $endDate)
    {
        return Booking::whereBetween('created_at', [$startDate, $endDate])
                     ->where('status', 'cancelled')
                     ->count();
    }

    private function getPaymentMethodsBreakdown($startDate, $endDate)
    {
        return DB::table('bookings')
                 ->whereBetween('created_at', [$startDate, $endDate])
                 ->whereIn('status', ['completed', 'active'])
                 ->select(
                     'payment_method',
                     DB::raw('COUNT(*) as count'),
                     DB::raw('SUM(total_amount) as total_amount'),
                     DB::raw('AVG(total_amount) as avg_amount')
                 )
                 ->groupBy('payment_method')
                 ->orderByDesc('total_amount')
                 ->get();
    }

    private function getMonthlyComparison()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $currentMonthRevenue = Booking::where('created_at', '>=', $currentMonth)
                                    ->whereIn('status', ['completed', 'active'])
                                    ->sum('total_amount');

        $lastMonthRevenue = Booking::whereBetween('created_at', [$lastMonth, $lastMonthEnd])
                                  ->whereIn('status', ['completed', 'active'])
                                  ->sum('total_amount');

        $growth = $lastMonthRevenue > 0 ? 
            round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 2) : 0;

        return [
            'current_month' => $currentMonthRevenue,
            'last_month' => $lastMonthRevenue,
            'growth_percentage' => $growth
        ];
    }

    private function getProfitMargins($startDate, $endDate)
    {
        // Simulação de cálculo de margem de lucro
        // Em um sistema real, você teria custos operacionais
        $totalRevenue = $this->getGrossRevenue($startDate, $endDate);
        $estimatedCosts = $totalRevenue * 0.3; // 30% de custos estimados
        $profit = $totalRevenue - $estimatedCosts;
        $profitMargin = $totalRevenue > 0 ? round(($profit / $totalRevenue) * 100, 2) : 0;

        return [
            'total_revenue' => $totalRevenue,
            'estimated_costs' => $estimatedCosts,
            'profit' => $profit,
            'profit_margin_percentage' => $profitMargin
        ];
    }

    private function getCustomerAnalytics($period)
    {
        $startDate = $this->getStartDate($period);

        return [
            'new_customers' => User::where('role', 'client')
                                 ->where('created_at', '>=', $startDate)
                                 ->count(),
            'repeat_customers' => DB::table('bookings')
                                    ->select('user_id')
                                    ->where('created_at', '>=', $startDate)
                                    ->groupBy('user_id')
                                    ->havingRaw('COUNT(*) > 1')
                                    ->get()
                                    ->count(),
            'avg_bookings_per_customer' => DB::table('bookings')
                                             ->where('created_at', '>=', $startDate)
                                             ->groupBy('user_id')
                                             ->select(DB::raw('COUNT(*) as booking_count'))
                                             ->avg('booking_count') ?? 0,
            'customer_lifetime_value' => DB::table('bookings')
                                           ->where('created_at', '>=', $startDate)
                                           ->whereIn('status', ['completed', 'active'])
                                           ->groupBy('user_id')
                                           ->select(DB::raw('SUM(total_amount) as total_spent'))
                                           ->avg('total_spent') ?? 0
        ];
    }

    private function getSeasonalTrends()
    {
        return DB::table('bookings')
                 ->whereIn('status', ['completed', 'active'])
                 ->where('created_at', '>=', Carbon::now()->subYear())
                 ->select(
                     DB::raw('MONTH(created_at) as month'),
                     DB::raw('COUNT(*) as bookings_count'),
                     DB::raw('SUM(total_amount) as revenue'),
                     DB::raw('AVG(total_amount) as avg_booking_value')
                 )
                 ->groupBy(DB::raw('MONTH(created_at)'))
                 ->orderBy('month')
                 ->get();
    }

    private function getVehiclePerformanceMetrics()
    {
        return DB::table('vehicles')
                 ->leftJoin('bookings', 'vehicles.id', '=', 'bookings.vehicle_id')
                 ->leftJoin('categories', 'vehicles.category_id', '=', 'categories.id')
                 ->select(
                     'vehicles.id',
                     'vehicles.brand',
                     'vehicles.model',
                     'categories.name as category',
                     DB::raw('COUNT(bookings.id) as total_bookings'),
                     DB::raw('SUM(CASE WHEN bookings.status IN ("completed", "active") THEN bookings.total_amount ELSE 0 END) as total_revenue'),
                     DB::raw('AVG(CASE WHEN bookings.status IN ("completed", "active") THEN bookings.total_amount ELSE NULL END) as avg_booking_value'),
                     DB::raw('SUM(CASE WHEN bookings.status = "cancelled" THEN 1 ELSE 0 END) as cancelled_bookings')
                 )
                 ->groupBy('vehicles.id', 'vehicles.brand', 'vehicles.model', 'categories.name')
                 ->orderByDesc('total_revenue')
                 ->limit(20)
                 ->get();
    }

    private function getPredictiveAnalytics()
    {
        // Previsão simples baseada em tendências históricas
        $last6Months = DB::table('bookings')
                         ->whereIn('status', ['completed', 'active'])
                         ->where('created_at', '>=', Carbon::now()->subMonths(6))
                         ->select(
                             DB::raw('YEAR(created_at) as year'),
                             DB::raw('MONTH(created_at) as month'),
                             DB::raw('SUM(total_amount) as revenue')
                         )
                         ->groupBy('year', 'month')
                         ->orderBy('year')
                         ->orderBy('month')
                         ->get();

        $avgMonthlyGrowth = 0;
        if ($last6Months->count() > 1) {
            $revenues = $last6Months->pluck('revenue')->toArray();
            $growthRates = [];
            
            for ($i = 1; $i < count($revenues); $i++) {
                if ($revenues[$i - 1] > 0) {
                    $growthRates[] = (($revenues[$i] - $revenues[$i - 1]) / $revenues[$i - 1]) * 100;
                }
            }
            
            $avgMonthlyGrowth = count($growthRates) > 0 ? array_sum($growthRates) / count($growthRates) : 0;
        }

        $lastMonthRevenue = $last6Months->last()->revenue ?? 0;
        $predictedNextMonth = $lastMonthRevenue * (1 + ($avgMonthlyGrowth / 100));

        return [
            'avg_monthly_growth_rate' => round($avgMonthlyGrowth, 2),
            'predicted_next_month_revenue' => round($predictedNextMonth, 2),
            'trend' => $avgMonthlyGrowth > 0 ? 'growing' : ($avgMonthlyGrowth < 0 ? 'declining' : 'stable')
        ];
    }

    private function getKPIMetrics($period)
    {
        $startDate = $this->getStartDate($period);
        
        $totalBookings = Booking::where('created_at', '>=', $startDate)->count();
        $completedBookings = Booking::where('created_at', '>=', $startDate)
                                   ->where('status', 'completed')
                                   ->count();
        $cancelledBookings = Booking::where('created_at', '>=', $startDate)
                                   ->where('status', 'cancelled')
                                   ->count();

        return [
            'booking_completion_rate' => $totalBookings > 0 ? 
                round(($completedBookings / $totalBookings) * 100, 2) : 0,
            'booking_cancellation_rate' => $totalBookings > 0 ? 
                round(($cancelledBookings / $totalBookings) * 100, 2) : 0,
            'fleet_utilization_rate' => $this->calculateFleetUtilization($startDate),
            'revenue_per_vehicle' => $this->calculateRevenuePerVehicle($startDate),
            'customer_satisfaction_score' => 4.2 // Placeholder - seria calculado com base em avaliações
        ];
    }

    private function calculateFleetUtilization($startDate)
    {
        $totalVehicles = Vehicle::count();
        $activeBookings = Booking::where('start_date', '>=', $startDate)
                                ->whereIn('status', ['active', 'completed'])
                                ->distinct('vehicle_id')
                                ->count();

        return $totalVehicles > 0 ? round(($activeBookings / $totalVehicles) * 100, 2) : 0;
    }

    private function calculateRevenuePerVehicle($startDate)
    {
        $totalRevenue = Booking::where('created_at', '>=', $startDate)
                              ->whereIn('status', ['completed', 'active'])
                              ->sum('total_amount');
        $totalVehicles = Vehicle::count();

        return $totalVehicles > 0 ? round($totalRevenue / $totalVehicles, 2) : 0;
    }
}