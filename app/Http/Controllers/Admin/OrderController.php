<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    public function index()
    {
        return view('admin.orders.index');
    }

    public function getData(Request $request)
    {
        $query = Order::query()->latest();

        $status = $request->input('status');
        if ($status && in_array($status, ['pending', 'processing', 'completed', 'canceled'])) {
            $query->where('status', $status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('order_date', function (Order $order) {
                return $order->created_at?->format('Y-m-d H:i');
            })
            ->addColumn('total_price', function (Order $order) {
                return number_format((float) $order->total_amount, 2);
            })
            ->editColumn('status', function (Order $order) {
                return ucfirst($order->status);
            })
            ->addColumn('action', function (Order $order) {
                return view('admin.orders.partials.actions', compact('order'))->render();
            })
            ->rawColumns(['action'])
            ->setRowId('id')
            ->make(true);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['success' => true, 'message' => 'Order deleted successfully.']);
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
        ]);

        return view('admin.orders.show', compact('order'));
    }
}
