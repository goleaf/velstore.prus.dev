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

        $totalSalesToday = Payment::query()
            ->whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('amount');

        $totalOrders = Order::query()->count();
        $completedOrders = Order::query()->where('status', 'completed')->count();
        $totalVendors = Vendor::query()->count();
        $totalCustomers = Customer::query()->count();

        $totalRevenue = Payment::query()->where('status', 'completed')->sum('amount');
        $averageOrderValue = $completedOrders > 0 ? round($totalRevenue / $completedOrders, 2) : 0.0;

        $refundsAmount = Refund::query()->where('status', 'completed')->sum('amount');

        $topProducts = Product::query()
            ->withCount(['reviews'])
            ->orderByDesc('reviews_count')
            ->limit(5)
            ->get(['id', 'slug', 'price']);

        // Last 7 days revenue trend
        $revenueTrend = Payment::query()
            ->selectRaw('DATE(created_at) as d, SUM(amount) as s')
            ->where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
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
                'orders_total' => $totalOrders,
                'orders_completed' => $completedOrders,
                'vendors_total' => $totalVendors,
                'customers_total' => $totalCustomers,
                'aov' => $averageOrderValue,
                'refunds_total' => $refundsAmount,
            ],
            'topProducts' => $topProducts,
            'revenueTrend' => $revenueTrend,
            'orderStatusBreakdown' => $orderStatusBreakdown,
        ]);
    }
}
