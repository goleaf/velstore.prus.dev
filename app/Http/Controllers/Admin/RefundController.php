<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\Refund;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = Arr::wrap($request->input('status', []));
        $statusFilter = array_values(array_filter($statusFilter, function ($status) {
            if (! is_string($status)) {
                return false;
            }

            return in_array(strtolower($status), Refund::STATUSES, true);
        }));

        $filters = [
            'status' => $statusFilter,
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'shop_id' => $request->input('shop_id'),
            'gateway_id' => $request->input('gateway_id'),
            'search' => $request->input('search_term'),
        ];

        $stats = [
            'total' => Refund::count(),
            'completed' => Refund::where('status', Refund::STATUS_COMPLETED)->count(),
            'refunded_amount' => Refund::where('status', Refund::STATUS_COMPLETED)->sum('amount'),
            'pending' => Refund::whereIn('status', [
                Refund::STATUS_PENDING,
                Refund::STATUS_REQUESTED,
                Refund::STATUS_APPROVED,
            ])->count(),
            'average_amount' => round((float) Refund::avg('amount'), 2),
        ];

        $statusOptions = Refund::statusOptions();

        $shopOptions = Shop::query()
            ->orderBy('name')
            ->pluck('name', 'id');

        $gatewayOptions = PaymentGateway::query()
            ->orderBy('name')
            ->pluck('name', 'id');

        $shopBreakdownQuery = Refund::query()
            ->select([
                'refunds.id as refund_id',
                'refunds.amount',
                'shops.id as shop_id',
                'shops.name as shop_name',
            ])
            ->join('payments', 'payments.id', '=', 'refunds.payment_id')
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->join('shops', 'shops.id', '=', 'products.shop_id')
            ->groupBy('refunds.id', 'refunds.amount', 'shops.id', 'shops.name');

        $shopBreakdown = DB::query()
            ->fromSub($shopBreakdownQuery, 'refund_shops')
            ->select([
                'shop_id',
                'shop_name',
                DB::raw('COUNT(*) as refund_count'),
                DB::raw('SUM(amount) as total_amount'),
            ])
            ->groupBy('shop_id', 'shop_name')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        return view('admin.refunds.index', compact(
            'filters',
            'stats',
            'statusOptions',
            'shopOptions',
            'gatewayOptions',
            'shopBreakdown'
        ));
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $statusFilter = Arr::wrap($request->input('status', []));
            $statusFilter = array_values(array_filter($statusFilter, function ($status) {
                if (! is_string($status)) {
                    return false;
                }

                return in_array(strtolower($status), Refund::STATUSES, true);
            }));

            $refunds = Refund::with([
                    'payment.gateway',
                    'payment.order.customer',
                    'payment.order.details.product.shop',
                ])
                ->select('refunds.*')
                ->withStatuses($statusFilter)
                ->createdBetween($request->input('date_from'), $request->input('date_to'))
                ->forShop($request->input('shop_id'))
                ->forGateway($request->input('gateway_id'))
                ->search($request->input('search_term'));

            return DataTables::of($refunds)
                ->addColumn('reference', function ($row) {
                    return $row->refund_id ?: __('cms.refunds.not_available');
                })
                ->editColumn('amount', function ($row) {
                    $amount = number_format((float) $row->amount, 2);
                    $currency = $row->currency ? strtoupper($row->currency) : '';

                    return trim($amount . ' ' . $currency);
                })
                ->editColumn('reason', function ($row) {
                    if (! $row->reason) {
                        return __('cms.refunds.not_available');
                    }

                    return Str::limit($row->reason, 80);
                })
                ->addColumn('payment', function ($row) {
                    if (! $row->payment) {
                        return '<span class="text-sm text-gray-400">' . e(__('cms.refunds.not_available')) . '</span>';
                    }

                    $payment = $row->payment;
                    $amount = number_format((float) $payment->amount, 2);
                    $statusClassMap = [
                        'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-500/20',
                        'pending' => 'bg-amber-50 text-amber-700 ring-amber-500/20',
                        'failed' => 'bg-rose-50 text-rose-700 ring-rose-500/20',
                    ];
                    $statusKey = strtolower((string) $payment->status);
                    $statusClass = $statusClassMap[$statusKey] ?? 'bg-gray-100 text-gray-700 ring-gray-500/10';
                    $statusLabels = [
                        'completed' => __('cms.payments.completed'),
                        'pending' => __('cms.payments.pending'),
                        'failed' => __('cms.payments.failed'),
                    ];
                    $statusLabel = $statusLabels[$statusKey] ?? ucfirst((string) $payment->status);
                    $gateway = $payment->gateway?->name;
                    $amountLabel = e(__('cms.payments.amount'));

                    $statusBadge = '<span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[11px] font-medium ring-1 ' . $statusClass . '"><span class="h-1.5 w-1.5 rounded-full bg-current"></span>' . e($statusLabel) . '</span>';
                    $gatewayMarkup = $gateway
                        ? '<span class="text-[11px] text-gray-500">' . e(__('cms.payments.gateway')) . ': ' . e($gateway) . '</span>'
                        : '';
                    $paymentLabel = e(__('cms.refunds.payment'));

                    return <<<HTML
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-semibold text-gray-900">{$paymentLabel} #{$payment->id}</span>
                            <span class="text-[11px] text-gray-500">{$amountLabel}: {$amount}</span>
                            {$statusBadge}
                            {$gatewayMarkup}
                        </div>
                    HTML;
                })
                ->editColumn('status', function ($row) {
                    $statusClass = Refund::badgeClassForStatus($row->status);
                    $label = Refund::labelForStatus($row->status);

                    return '<span class="inline-flex items-center gap-2 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ' . $statusClass . '"><span class="h-1.5 w-1.5 rounded-full bg-current"></span>' . e($label) . '</span>';
                })
                ->addColumn('shop', function ($row) {
                    $order = $row->payment?->order;

                    if (! $order) {
                        return __('cms.refunds.not_available');
                    }

                    $shop = $order->details
                        ->map(fn ($detail) => $detail->product?->shop?->name)
                        ->filter()
                        ->unique()
                        ->values()
                        ->first();

                    return $shop ?? __('cms.refunds.not_available');
                })
                ->addColumn('customer', function ($row) {
                    $order = $row->payment?->order;

                    if (! $order) {
                        return __('cms.refunds.not_available');
                    }

                    $customer = $order->customer;

                    if ($customer) {
                        return trim($customer->name . ' • ' . $customer->email);
                    }

                    if ($order->guest_email) {
                        return $order->guest_email;
                    }

                    return __('cms.refunds.not_available');
                })
                ->addColumn('action', function ($row) {
                    $showRoute = route('admin.refunds.show', $row->id);
                    $viewLabel = e(__('cms.messages.view_details'));
                    $deleteLabel = e(__('cms.refunds.delete'));

                    return <<<HTML
                        <div class="flex flex-col gap-2">
                            <button type="button"
                                    class="btn btn-outline btn-sm w-full btn-view-refund"
                                    data-url="{$showRoute}" title="{$viewLabel}">
                                {$viewLabel}
                            </button>
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm w-full btn-delete-refund"
                                    data-id="{$row->id}" title="{$deleteLabel}">
                                {$deleteLabel}
                            </button>
                        </div>
                    HTML;
                })
                ->rawColumns(['payment', 'status', 'action'])
                ->make(true);
        }
    }

    public function show($id)
    {
        $refund = Refund::with([
            'payment.gateway',
            'payment.order.customer',
            'payment.order.details.product.translation',
            'payment.order.details.product.shop',
        ])->findOrFail($id);

        return view('admin.refunds.show', compact('refund'));
    }

    public function destroy(Refund $refund)
    {
        $refund->delete();

        return response()->json(['success' => true, 'message' => 'Refund deleted successfully.']);
    }
}
