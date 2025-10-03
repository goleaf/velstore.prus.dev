<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends Controller
{
    public function index()
    {
        $statusOptions = collect(Payment::STATUSES)->mapWithKeys(function ($status) {
            $translationKey = 'cms.payments.' . $status;
            $label = __($translationKey);

            if ($label === $translationKey) {
                $label = ucfirst(str_replace('_', ' ', $status));
            }

            return [$status => $label];
        });

        $gateways = PaymentGateway::query()->orderBy('name')->get(['id', 'name']);
        $shops = Shop::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.payments.index', [
            'statusOptions' => $statusOptions,
            'gateways' => $gateways,
            'shops' => $shops,
        ]);
    }

    public function getData(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $payments = Payment::query()
            ->with(['order.customer', 'order.details.product.shop', 'gateway'])
            ->select('payments.*');

        $status = $request->input('status');
        if ($status && in_array($status, Payment::STATUSES, true)) {
            $payments->forStatus($status);
        }

        $payments->forGateway($request->integer('gateway_id'));
        $payments->forShop($request->integer('shop_id'));

        $dateFrom = $this->parseDate($request->input('date_from'));
        $dateTo = $this->parseDate($request->input('date_to'), true);

        if ($dateFrom) {
            $payments->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $payments->where('created_at', '<=', $dateTo);
        }

        return DataTables::of($payments)
            ->addColumn('order', function ($row) {
                if (! $row->order) {
                    return '—';
                }

                $orderRoute = route('admin.orders.show', $row->order->id);

                return '<a href="' . e($orderRoute) . '" class="text-decoration-none">#' . e($row->order->id) . '</a>';
            })
            ->addColumn('customer', function ($row) {
                return $row->customer_display_name ?? '—';
            })
            ->addColumn('shops', function ($row) {
                if (empty($row->shop_names)) {
                    return '—';
                }

                return collect($row->shop_names)
                    ->map(fn ($name) => e($name))
                    ->implode(', ');
            })
            ->addColumn('gateway', fn ($row) => $row->gateway?->name ?? '—')
            ->editColumn('amount', function ($row) {
                $amount = number_format((float) $row->amount, 2);

                return $row->currency ? $amount . ' ' . $row->currency : $amount;
            })
            ->addColumn('status_badge', function ($row) {
                $statusVariants = [
                    'completed' => 'success',
                    'pending' => 'warning text-dark',
                    'failed' => 'danger',
                    'processing' => 'info text-dark',
                    'refunded' => 'primary text-dark',
                ];

                $statusKey = $row->status ?? 'unknown';
                $variant = $statusVariants[$statusKey] ?? 'secondary';
                $translationKey = 'cms.payments.' . $statusKey;
                $label = __($translationKey);

                if ($label === $translationKey) {
                    $label = ucfirst(str_replace('_', ' ', $statusKey));
                }

                return '<span class="badge bg-' . $variant . '">' . e($label) . '</span>';
            })
            ->editColumn('created_at', fn ($row) => optional($row->created_at)->format('d M Y, h:i A') ?? '—')
            ->addColumn('action', function ($row) {
                $showRoute = route('admin.payments.show', $row->id);

                return '<div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary btn-view-payment" data-url="' . e($showRoute) . '">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-delete-payment" data-id="' . e($row->id) . '">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>';
            })
            ->rawColumns(['action', 'order', 'status_badge'])
            ->make(true);
    }

    public function show($id)
    {
        $payment = Payment::with(['order.customer', 'order.details.product.shop', 'gateway'])->findOrFail($id);

        return view('admin.payments.show', compact('payment'));
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return response()->json(['success' => true, 'message' => 'Payment deleted successfully.']);
    }

    protected function parseDate(?string $date, bool $endOfDay = false): ?Carbon
    {
        if (blank($date)) {
            return null;
        }

        try {
            $parsed = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception) {
            return null;
        }

        return $endOfDay ? $parsed->endOfDay() : $parsed->startOfDay();
    }
}
