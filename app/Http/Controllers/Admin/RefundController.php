<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class RefundController extends Controller
{
    public function index()
    {
        return view('admin.refunds.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $refunds = Refund::with(['payment.gateway'])->select('refunds.*');

            return DataTables::of($refunds)
                ->editColumn('amount', fn($row) => number_format((float) $row->amount, 2))
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
                    $statusKey = strtolower((string) $row->status);
                    $statusLabels = [
                        'completed' => __('cms.refunds.completed'),
                        'pending' => __('cms.refunds.pending'),
                        'failed' => __('cms.refunds.failed'),
                    ];
                    $statusClassMap = [
                        'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-500/20',
                        'pending' => 'bg-amber-50 text-amber-700 ring-amber-500/20',
                        'failed' => 'bg-rose-50 text-rose-700 ring-rose-500/20',
                    ];

                    $label = $statusLabels[$statusKey] ?? ucfirst($statusKey);
                    $statusClass = $statusClassMap[$statusKey] ?? 'bg-gray-100 text-gray-700 ring-gray-500/10';

                    return '<span class="inline-flex items-center gap-2 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ' . $statusClass . '"><span class="h-1.5 w-1.5 rounded-full bg-current"></span>' . e($label) . '</span>';
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
        $refund = Refund::with('payment.order', 'payment.gateway')->findOrFail($id);

        return view('admin.refunds.show', compact('refund'));
    }

    public function destroy(Refund $refund)
    {
        $refund->delete();

        return response()->json(['success' => true, 'message' => 'Refund deleted successfully.']);
    }
}
