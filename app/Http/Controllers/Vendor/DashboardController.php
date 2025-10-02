<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // ✅ Use vendor authentication middleware
        $this->middleware('auth.vendor');
    }

    /**
     * Show the vendor dashboard.
     */
    public function index()
    {
        $vendor = Auth::guard('vendor')->user();

        $vendorProductIds = Product::query()
            ->where('vendor_id', $vendor?->id)
            ->pluck('id');

        $vendorOrders = Order::query()
            ->whereHas('details', function ($q) use ($vendorProductIds) {
                $q->whereIn('product_id', $vendorProductIds);
            });

        $ordersTotal = (clone $vendorOrders)->count();
        $ordersCompleted = (clone $vendorOrders)->where('status', 'completed')->count();

        $revenue = Payment::query()
            ->where('status', 'completed')
            ->whereHas('order.details', function ($q) use ($vendorProductIds) {
                $q->whereIn('product_id', $vendorProductIds);
            })
            ->sum('amount');

        $revenueTrend = Payment::query()
            ->selectRaw('DATE(created_at) as d, SUM(amount) as s')
            ->where('status', 'completed')
            ->whereHas('order.details', function ($q) use ($vendorProductIds) {
                $q->whereIn('product_id', $vendorProductIds);
            })
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->map(fn ($row) => ['date' => $row->d, 'amount' => (float) $row->s]);

        return view('vendor.dashboard', [
            'kpi' => [
                'orders_total' => $ordersTotal,
                'orders_completed' => $ordersCompleted,
                'revenue' => $revenue,
                'products' => $vendorProductIds->count(),
            ],
            'revenueTrend' => $revenueTrend,
        ]);
    }
}
