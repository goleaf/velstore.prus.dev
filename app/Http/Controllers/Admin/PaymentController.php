<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private const STATUS_BADGES = [
        'completed' => 'badge badge-success',
        'pending' => 'badge badge-warning',
        'failed' => 'badge badge-danger',
        'processing' => 'badge badge-info',
        'refunded' => 'badge badge-gray',
    ];

    public function index(Request $request)
    {
        $statusOptions = collect(Payment::STATUSES)->mapWithKeys(function ($status) {
            $translationKey = 'cms.payments.' . $status;
            $label = __($translationKey);

            if ($label === $translationKey) {
                $label = ucfirst(str_replace('_', ' ', $status));
            }

            return [$status => $label];
        });

        $rawStatus = $request->string('status')->toString();
        $status = in_array($rawStatus, Payment::STATUSES, true) ? $rawStatus : null;

        $gatewayId = $request->integer('gateway_id') ?: null;
        $shopId = $request->integer('shop_id') ?: null;

        $dateFromInput = $request->input('date_from');
        $dateFrom = $this->parseDate($dateFromInput);
        if (! $dateFrom) {
            $dateFromInput = null;
        }

        $dateToInput = $request->input('date_to');
        $dateTo = $this->parseDate($dateToInput, true);
        if (! $dateTo) {
            $dateToInput = null;
        }

        $paymentsQuery = Payment::query()
            ->with(['order.customer', 'order.details.product.shop', 'gateway'])
            ->select('payments.*');

        if ($status) {
            $paymentsQuery->forStatus($status);
        }

        $paymentsQuery
            ->forGateway($gatewayId)
            ->forShop($shopId);

        if ($dateFrom) {
            $paymentsQuery->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $paymentsQuery->where('created_at', '<=', $dateTo);
        }

        $paginatedQuery = clone $paymentsQuery;

        $payments = $paginatedQuery
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $metricsQuery = clone $paymentsQuery;

        $metrics = [
            'total' => (clone $metricsQuery)->count(),
            'completed' => (clone $metricsQuery)->where('status', 'completed')->count(),
            'failed' => (clone $metricsQuery)->where('status', 'failed')->count(),
        ];

        $gateways = PaymentGateway::query()->orderBy('name')->get(['id', 'name']);
        $shops = Shop::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.payments.index', [
            'statusOptions' => $statusOptions,
            'gateways' => $gateways,
            'shops' => $shops,
            'payments' => $payments,
            'filters' => [
                'status' => $status ?? '',
                'gateway_id' => $gatewayId ? (string) $gatewayId : '',
                'shop_id' => $shopId ? (string) $shopId : '',
                'date_from' => $dateFromInput ?? '',
                'date_to' => $dateToInput ?? '',
            ],
            'statusBadges' => self::STATUS_BADGES,
            'metrics' => $metrics,
        ]);
    }

    public function show($id)
    {
        $payment = Payment::with(['order.customer', 'order.details.product.shop', 'gateway'])->findOrFail($id);

        $statusKey = $payment->status ?? 'unknown';
        $statusBadge = self::STATUS_BADGES[$statusKey] ?? 'badge badge-gray';
        $statusTranslationKey = 'cms.payments.' . $statusKey;
        $statusLabel = __($statusTranslationKey);

        if ($statusLabel === $statusTranslationKey) {
            $statusLabel = ucfirst(str_replace('_', ' ', (string) $statusKey));
        }

        return view('admin.payments.show', [
            'payment' => $payment,
            'statusBadge' => $statusBadge,
            'statusLabel' => $statusLabel,
        ]);
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => __('cms.payments.deleted'),
        ]);
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
