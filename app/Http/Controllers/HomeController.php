<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Vendor;
use Carbon\Carbon;

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
        $ordersCancelled = Order::query()->where('status', 'cancelled')->count();

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

        $topProducts = Product::query()
            ->withCount(['reviews'])
            ->with(['translation' => function ($query) {
                $query->select('product_id', 'name');
            }])
            ->orderByDesc('reviews_count')
            ->limit(5)
            ->get(['id', 'slug', 'price']);

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
        ]);
    }
}
