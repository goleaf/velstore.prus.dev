<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected array $statusFilters = [
        'pending',
        'processing',
        'completed',
        'canceled',
    ];

    public function index(Request $request)
    {
        $status = $request->query('status');
        $ordersQuery = Order::query()->latest();

        if (is_string($status) && in_array($status, $this->statusFilters, true)) {
            $ordersQuery->where('status', $status);
        } else {
            $status = '';
        }

        $orders = $ordersQuery->paginate(10)->withQueryString();

        $statusFilterLabels = [
            '' => __('cms.orders.all_orders'),
            'pending' => __('cms.orders.pending_orders'),
            'processing' => __('cms.orders.processing_orders'),
            'completed' => __('cms.orders.completed_orders'),
            'canceled' => __('cms.orders.cancelled_orders'),
        ];

        return view('admin.orders.index', [
            'orders' => $orders,
            'statusFilters' => $statusFilterLabels,
            'currentStatus' => $status,
        ]);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json([
            'success' => true,
            'message' => __('cms.orders.deleted_success'),
        ]);
    }

    public function show(Order $order)
    {
        $order->load([
            'customer',
            'shippingAddress',
            'details.product.translation',
            'details.product.brand.translation',
            'details.product.category.translation',
            'payments.gateway',
            'payments.refunds',
            'statusUpdates',
            'notes',
        ]);

        return view('admin.orders.show', compact('order'));
    }
}
