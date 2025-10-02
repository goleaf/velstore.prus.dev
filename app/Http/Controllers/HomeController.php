<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Removed auth middleware - no user authentication required
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $historyDays = 6; // 7 day window including today
        $weeklyWindows = 5; // 6 week window including current week
        $historyStartDate = Carbon::now()->copy()->subDays($historyDays)->startOfDay();
        $historyWeekStart = Carbon::now()->copy()->subWeeks($weeklyWindows)->startOfWeek();

        $totalSalesToday = Payment::query()
            ->whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('amount');

        $totalSalesYesterday = Payment::query()
            ->whereDate('created_at', $yesterday)
            ->where('status', 'completed')
            ->sum('amount');

        $totalOrders = Order::query()->count();
        $completedOrders = Order::query()->where('status', 'completed')->count();
        $ordersPending = Order::query()->where('status', 'pending')->count();
        $ordersProcessing = Order::query()->where('status', 'processing')->count();
        $ordersCancelled = Order::query()->where('status', 'canceled')->count();

        $totalVendors = Vendor::query()->count();
        $totalCustomers = Customer::query()->count();

        $totalRevenue = Payment::query()->where('status', 'completed')->sum('amount');
        $averageOrderValue = $completedOrders > 0 ? round($totalRevenue / $completedOrders, 2) : 0.0;

        $refundsAmount = Refund::query()->where('status', 'completed')->sum('amount');
        $refundRate = $totalRevenue > 0 ? round(($refundsAmount / $totalRevenue) * 100, 2) : 0.0;
        $netRevenue = round($totalRevenue - $refundsAmount, 2);

        $weeklyRevenue = Payment::query()
            ->where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->sum('amount');

        $previousWeeklyRevenue = Payment::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [
                Carbon::now()->subDays(13)->startOfDay(),
                Carbon::now()->subDays(7)->endOfDay(),
            ])
            ->sum('amount');

        $weeklyRevenueChange = $previousWeeklyRevenue > 0
            ? round((($weeklyRevenue - $previousWeeklyRevenue) / $previousWeeklyRevenue) * 100, 1)
            : null;

        $ordersCompletionRate = $totalOrders > 0
            ? round(($completedOrders / $totalOrders) * 100, 1)
            : 0.0;

        $monthStart = Carbon::now()->startOfMonth();
        $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $customersThisMonth = Customer::query()
            ->whereBetween('created_at', [$monthStart, Carbon::now()])
            ->count();

        $customersPreviousMonth = Customer::query()
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->count();

        $customersGrowth = $customersPreviousMonth > 0
            ? round((($customersThisMonth - $customersPreviousMonth) / $customersPreviousMonth) * 100, 1)
            : ($customersThisMonth > 0 ? null : 0.0);

        $salesAggregates = OrderDetail::query()
            ->select('product_id',
                DB::raw('SUM(quantity) as units_sold'),
                DB::raw('SUM(quantity * price) as revenue_generated')
            )
            ->groupBy('product_id');

        $topProducts = Product::query()
            ->leftJoinSub($salesAggregates, 'sales', 'sales.product_id', '=', 'products.id')
            ->with([
                'translation' => function ($query) {
                    $query->select('product_id', 'name');
                },
            ])
            ->withCount([
                'reviews as reviews_count' => fn ($query) => $query->approved(),
            ])
            ->withAvg([
                'reviews as average_rating' => fn ($query) => $query->approved(),
            ], 'rating')
            ->select(
                'products.*',
                DB::raw('COALESCE(sales.units_sold, 0) as units_sold'),
                DB::raw('COALESCE(sales.revenue_generated, 0) as revenue_generated')
            )
            ->orderByDesc(DB::raw('COALESCE(sales.revenue_generated, 0)'))
            ->limit(5)
            ->get()
            ->map(function ($product) {
                $product->average_rating = round((float) $product->average_rating, 1);

                return $product;
            });

        // Last 7 days revenue trend
        $revenueTrend = Payment::query()
            ->selectRaw('DATE(created_at) as d, SUM(amount) as s')
            ->where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->map(fn($row) => ['date' => $row->d, 'amount' => (float) $row->s]);

        // Order status breakdown
        $orderStatusBreakdown = Order::query()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        // Chart datasets for sparkline graphs
        $dateRange = collect(range(0, $historyDays))->map(fn ($offset) => Carbon::now()->copy()->subDays($historyDays - $offset)->startOfDay());
        $dateLabels = $dateRange->map(fn ($date) => $date->format('M d'));

        $paymentsByDay = Payment::query()
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->where('status', 'completed')
            ->where('created_at', '>=', $historyStartDate)
            ->groupBy('date')
            ->pluck('total', 'date');

        $refundsByDay = Refund::query()
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->where('status', 'completed')
            ->where('created_at', '>=', $historyStartDate)
            ->groupBy('date')
            ->pluck('total', 'date');

        $completedOrdersByDay = Order::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('status', 'completed')
            ->where('created_at', '>=', $historyStartDate)
            ->groupBy('date')
            ->pluck('total', 'date');

        $ordersByDay = Order::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', $historyStartDate)
            ->groupBy('date')
            ->pluck('total', 'date');

        $openOrdersByDay = Order::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->whereIn('status', ['pending', 'processing'])
            ->where('created_at', '>=', $historyStartDate)
            ->groupBy('date')
            ->pluck('total', 'date');

        $customersByDay = Customer::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', $historyStartDate)
            ->groupBy('date')
            ->pluck('total', 'date');

        $vendorsByDay = Vendor::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', $historyStartDate)
            ->groupBy('date')
            ->pluck('total', 'date');

        $dailyRevenueSeries = $dateRange->map(fn ($date) => round((float) ($paymentsByDay[$date->toDateString()] ?? 0), 2));
        $dailyRefundSeries = $dateRange->map(fn ($date) => round((float) ($refundsByDay[$date->toDateString()] ?? 0), 2));
        $dailyNetRevenueSeries = $dateRange->map(fn ($date) => round((float) (($paymentsByDay[$date->toDateString()] ?? 0) - ($refundsByDay[$date->toDateString()] ?? 0)), 2));
        $dailyCompletedOrdersSeries = $dateRange->map(fn ($date) => (int) ($completedOrdersByDay[$date->toDateString()] ?? 0));
        $dailyOrdersSeries = $dateRange->map(fn ($date) => (int) ($ordersByDay[$date->toDateString()] ?? 0));
        $dailyCompletionRateSeries = $dateRange->values()->map(function ($date, $index) use ($dailyCompletedOrdersSeries, $dailyOrdersSeries) {
            $total = $dailyOrdersSeries[$index] ?? 0;
            $completed = $dailyCompletedOrdersSeries[$index] ?? 0;

            return $total > 0 ? round(($completed / $total) * 100, 1) : 0;
        });

        $dailyAovSeries = $dateRange->map(function ($date) use ($paymentsByDay, $completedOrdersByDay) {
            $key = $date->toDateString();
            $completed = $completedOrdersByDay[$key] ?? 0;

            return $completed > 0
                ? round(($paymentsByDay[$key] ?? 0) / $completed, 2)
                : 0;
        });

        $dailyOpenOrdersSeries = $dateRange->map(fn ($date) => (int) ($openOrdersByDay[$date->toDateString()] ?? 0));
        $dailyCustomersSeries = $dateRange->map(fn ($date) => (int) ($customersByDay[$date->toDateString()] ?? 0));
        $dailyVendorsSeries = $dateRange->map(fn ($date) => (int) ($vendorsByDay[$date->toDateString()] ?? 0));

        $weeklyRevenueRaw = Payment::query()
            ->selectRaw("DATE_FORMAT(created_at, '%x-%v') as period, SUM(amount) as total")
            ->where('status', 'completed')
            ->where('created_at', '>=', $historyWeekStart)
            ->groupBy('period')
            ->pluck('total', 'period');

        $weeklyLabels = collect(range(0, $weeklyWindows))->map(function ($offset) use ($weeklyWindows) {
            $weekStart = Carbon::now()->copy()->subWeeks($weeklyWindows - $offset)->startOfWeek();

            return $weekStart->format('M j');
        });

        $weeklyRevenueSeries = collect(range(0, $weeklyWindows))->map(function ($offset) use ($weeklyWindows, $weeklyRevenueRaw) {
            $weekStart = Carbon::now()->copy()->subWeeks($weeklyWindows - $offset)->startOfWeek();
            $key = sprintf('%d-%02d', $weekStart->isoWeekYear, $weekStart->isoWeek);

            return round((float) ($weeklyRevenueRaw[$key] ?? 0), 2);
        });

        $cardCharts = [
            'sales_today' => [
                'labels' => $dateLabels,
                'values' => $dailyRevenueSeries,
                'color' => '#059669',
                'background' => 'rgba(5, 150, 105, 0.12)',
                'label' => __('cms.dashboard.chart_label_daily_revenue'),
            ],
            'weekly_revenue' => [
                'labels' => $weeklyLabels,
                'values' => $weeklyRevenueSeries,
                'color' => '#2563eb',
                'background' => 'rgba(37, 99, 235, 0.12)',
                'label' => __('cms.dashboard.chart_label_weekly_revenue'),
            ],
            'net_revenue' => [
                'labels' => $dateLabels,
                'values' => $dailyNetRevenueSeries,
                'color' => '#7c3aed',
                'background' => 'rgba(124, 58, 237, 0.12)',
                'label' => __('cms.dashboard.chart_label_net_revenue'),
            ],
            'aov' => [
                'labels' => $dateLabels,
                'values' => $dailyAovSeries,
                'color' => '#f59e0b',
                'background' => 'rgba(245, 158, 11, 0.15)',
                'label' => __('cms.dashboard.chart_label_average_order_value'),
            ],
            'completion_rate' => [
                'labels' => $dateLabels,
                'values' => $dailyCompletionRateSeries,
                'color' => '#22c55e',
                'background' => 'rgba(34, 197, 94, 0.12)',
                'label' => __('cms.dashboard.chart_label_order_completion'),
                'suffix' => '%',
            ],
            'open_orders' => [
                'labels' => $dateLabels,
                'values' => $dailyOpenOrdersSeries,
                'color' => '#fb923c',
                'background' => 'rgba(251, 146, 60, 0.14)',
                'label' => __('cms.dashboard.chart_label_open_orders'),
            ],
            'customers' => [
                'labels' => $dateLabels,
                'values' => $dailyCustomersSeries,
                'color' => '#0ea5e9',
                'background' => 'rgba(14, 165, 233, 0.12)',
                'label' => __('cms.dashboard.chart_label_customers'),
            ],
            'vendors' => [
                'labels' => $dateLabels,
                'values' => $dailyVendorsSeries,
                'color' => '#6366f1',
                'background' => 'rgba(99, 102, 241, 0.12)',
                'label' => __('cms.dashboard.chart_label_vendors'),
            ],
        ];

        $productInsights = [];
        $topRevenueProduct = $topProducts
            ->filter(fn ($product) => ($product->revenue_generated ?? 0) > 0)
            ->sortByDesc('revenue_generated')
            ->first();
        if ($topRevenueProduct) {
            $productInsights[] = __('cms.dashboard.insight_product_top_revenue', [
                'name' => optional($topRevenueProduct->translation)->name ?? $topRevenueProduct->slug,
                'value' => number_format($topRevenueProduct->revenue_generated, 2),
            ]);
        }

        $topUnitsProduct = $topProducts->sortByDesc('units_sold')->first();
        if ($topUnitsProduct && ($topUnitsProduct->units_sold ?? 0) > 0) {
            $productInsights[] = __('cms.dashboard.insight_product_top_units', [
                'name' => optional($topUnitsProduct->translation)->name ?? $topUnitsProduct->slug,
                'value' => number_format($topUnitsProduct->units_sold),
            ]);
        }

        $topRatingProduct = $topProducts->sortByDesc('average_rating')->first();
        if ($topRatingProduct && ($topRatingProduct->average_rating ?? 0) > 0) {
            $productInsights[] = __('cms.dashboard.insight_product_top_rating', [
                'name' => optional($topRatingProduct->translation)->name ?? $topRatingProduct->slug,
                'value' => number_format($topRatingProduct->average_rating, 1),
            ]);
        }

        return view('admin.home', [
            'kpi' => [
                'sales_today' => $totalSalesToday,
                'sales_yesterday' => $totalSalesYesterday,
                'weekly_revenue' => $weeklyRevenue,
                'weekly_revenue_change' => $weeklyRevenueChange,
                'orders_total' => $totalOrders,
                'orders_completed' => $completedOrders,
                'orders_completion_rate' => $ordersCompletionRate,
                'orders_pending' => $ordersPending,
                'orders_processing' => $ordersProcessing,
                'orders_cancelled' => $ordersCancelled,
                'vendors_total' => $totalVendors,
                'customers_total' => $totalCustomers,
                'customers_month' => $customersThisMonth,
                'customers_growth' => $customersGrowth,
                'aov' => $averageOrderValue,
                'total_revenue' => $totalRevenue,
                'net_revenue' => $netRevenue,
                'refunds_total' => $refundsAmount,
                'refund_rate' => $refundRate,
            ],
            'topProducts' => $topProducts,
            'revenueTrend' => $revenueTrend,
            'orderStatusBreakdown' => $orderStatusBreakdown,
            'cardCharts' => $cardCharts,
            'productInsights' => $productInsights,
        ]);
    }
}
