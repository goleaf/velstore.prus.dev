<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentGatewayRequest;
use App\Http\Requests\Admin\UpdatePaymentGatewayRequest;
use App\Models\PaymentGateway;
use App\Services\Admin\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PaymentGatewayController extends Controller
{
    public function __construct(private readonly PaymentGatewayService $paymentGatewayService)
    {
    }

    public function index(Request $request): View
    {
        $stats = [
            'total' => PaymentGateway::query()->count(),
            'active' => PaymentGateway::query()->where('is_active', true)->count(),
            'inactive' => PaymentGateway::query()->where('is_active', false)->count(),
        ];

        return view('admin.payment_gateways.index', [
            'statusFilter' => $request->query('status'),
            'stats' => $stats,
        ]);
    }

    public function getData(Request $request): JsonResponse
    {
        $gateways = PaymentGateway::query()
            ->withCount('configs')
            ->select('payment_gateways.*')
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->string('status') === 'active') {
                    $query->where('is_active', true);
                }

                if ($request->string('status') === 'inactive') {
                    $query->where('is_active', false);
                }
            });

        return DataTables::of($gateways)
            ->addColumn('status_badge', function (PaymentGateway $gateway) {
                $statusLabel = $gateway->is_active
                    ? '<span class="badge bg-success">' . e(__('cms.payment_gateways.active')) . '</span>'
                    : '<span class="badge bg-danger">' . e(__('cms.payment_gateways.inactive')) . '</span>';

                return $statusLabel;
            })
            ->addColumn('configs_count', fn (PaymentGateway $gateway) => $gateway->configs_count)
            ->addColumn('updated_at_for_humans', fn (PaymentGateway $gateway) => optional($gateway->updated_at)->diffForHumans())
            ->addColumn('action', function (PaymentGateway $gateway) {
                $editRoute = route('admin.payment-gateways.edit', $gateway);
                $toggleRoute = route('admin.payment-gateways.toggle', $gateway);
                $deleteLabel = e(__('cms.payment_gateways.delete'));
                $editLabel = e(__('cms.payment_gateways.edit_button'));
                $toggleLabel = $gateway->is_active
                    ? e(__('cms.payment_gateways.deactivate_button'))
                    : e(__('cms.payment_gateways.activate_button'));

                return <<<HTML
                    <div class="d-flex flex-column gap-2">
                        <a href="{$editRoute}" class="btn btn-outline-primary btn-sm">{$editLabel}</a>
                        <button type="button" class="btn btn-outline-secondary btn-sm btn-toggle-status" data-url="{$toggleRoute}">
                            {$toggleLabel}
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-delete-gateway" data-id="{$gateway->getKey()}">
                            {$deleteLabel}
                        </button>
                    </div>
                HTML;
            })
            ->rawColumns(['status_badge', 'action'])
            ->with([
                'stats' => [
                    'total' => PaymentGateway::query()->count(),
                    'active' => PaymentGateway::query()->where('is_active', true)->count(),
                    'inactive' => PaymentGateway::query()->where('is_active', false)->count(),
                ],
            ])
            ->make(true);
    }

    public function create(): View
    {
        $paymentGateway = new PaymentGateway(['is_active' => true]);
        $paymentGateway->setRelation('configs', collect());

        return view('admin.payment_gateways.create', compact('paymentGateway'));
    }

    public function store(StorePaymentGatewayRequest $request): RedirectResponse
    {
        $gateway = $this->paymentGatewayService->create($request->validated());

        return redirect()
            ->route('admin.payment-gateways.edit', $gateway)
            ->with('success', __('cms.payment_gateways.created'));
    }

    public function edit(PaymentGateway $paymentGateway): View
    {
        $paymentGateway->load('configs');

        return view('admin.payment_gateways.edit', compact('paymentGateway'));
    }

    public function update(UpdatePaymentGatewayRequest $request, PaymentGateway $paymentGateway): RedirectResponse
    {
        $this->paymentGatewayService->update($paymentGateway, $request->validated());

        return redirect()
            ->route('admin.payment-gateways.edit', $paymentGateway)
            ->with('success', __('cms.payment_gateways.updated'));
    }

    public function destroy(PaymentGateway $paymentGateway): JsonResponse
    {
        $paymentGateway->delete();

        return response()->json([
            'success' => true,
            'message' => __('cms.payment_gateways.deleted_message'),
        ]);
    }

    public function toggle(PaymentGateway $paymentGateway): JsonResponse
    {
        $paymentGateway->is_active = ! $paymentGateway->is_active;
        $paymentGateway->save();

        return response()->json([
            'success' => true,
            'message' => $paymentGateway->is_active
                ? __('cms.payment_gateways.activated_message')
                : __('cms.payment_gateways.deactivated_message'),
        ]);
    }
}
