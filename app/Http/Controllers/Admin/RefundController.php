<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\Refund;
use App\Models\Shop;
use App\Support\Refunds\RefundDashboardService;
use App\Support\Refunds\RefundFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class RefundController extends Controller
{
    public function __construct(private readonly RefundDashboardService $dashboardService)
    {
    }

    public function index(Request $request)
    {
        $filters = RefundFilters::fromRequest($request);

        $summary = $this->dashboardService->summary($filters);
        $statusOptions = Refund::statusOptions();

        $shopOptions = Shop::query()
            ->orderBy('name')
            ->pluck('name', 'id');

        $gatewayOptions = PaymentGateway::query()
            ->orderBy('name')
            ->pluck('name', 'id');

        $shopBreakdown = $this->dashboardService->shopBreakdown($filters);
        $statusDistribution = $this->dashboardService->statusDistribution($filters);
        $recentRefunds = $this->dashboardService->recentRefunds($filters);

        return view('admin.refunds.index', [
            'filters' => $filters->toArray(),
            'summary' => $summary,
            'statusOptions' => $statusOptions,
            'shopOptions' => $shopOptions,
            'gatewayOptions' => $gatewayOptions,
            'shopBreakdown' => $shopBreakdown,
            'statusDistribution' => $statusDistribution,
            'recentRefunds' => $recentRefunds,
        ]);
    }

    public function getData(Request $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $filters = RefundFilters::fromRequest($request);

        $refunds = $this->dashboardService->query($filters)
            ->with([
                'payment.gateway',
                'payment.order.customer',
                'payment.order.details.product.shop',
            ])
            ->select('refunds.*');

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
            ->editColumn('created_at', function ($row) {
                return optional($row->created_at)->format('Y-m-d H:i');
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

    public function export(Request $request)
    {
        $filters = RefundFilters::fromRequest($request);
        $fileName = 'refunds-' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $columns = [
            'ID',
            'Reference',
            'Amount',
            'Currency',
            'Status',
            'Reason',
            'Payment ID',
            'Gateway',
            'Customer',
            'Shop',
            'Created At',
        ];

        $responseCallback = function () use ($filters, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            $this->dashboardService->query($filters)
                ->with([
                    'payment.gateway',
                    'payment.order.customer',
                    'payment.order.details.product.shop',
                ])
                ->orderBy('refunds.id')
                ->chunkById(200, function ($refunds) use ($handle) {
                    foreach ($refunds as $refund) {
                        $payment = $refund->payment;
                        $order = $payment?->order;

                        $shop = $order?->details
                            ->map(fn ($detail) => $detail->product?->shop?->name)
                            ->filter()
                            ->unique()
                            ->values()
                            ->first();

                        $customer = $order?->customer;
                        $customerLabel = $customer
                            ? trim($customer->name . ' - ' . $customer->email)
                            : ($order?->guest_email ?? '');

                        fputcsv($handle, [
                            $refund->id,
                            $refund->refund_id,
                            number_format((float) $refund->amount, 2),
                            strtoupper((string) $refund->currency),
                            Refund::labelForStatus($refund->status),
                            $refund->reason ?? '',
                            $payment?->id,
                            $payment?->gateway?->name,
                            $customerLabel,
                            $shop,
                            optional($refund->created_at)->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        };

        return response()->stream($responseCallback, 200, $headers);
    }
}
