<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Shop;
use App\Services\Admin\OrderService as AdminOrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected array $statusFilters = [
        'pending',
        'processing',
        'completed',
        'canceled',
    ];

    protected array $statusFilterTranslationKeys = [
        'pending' => 'cms.orders.pending_orders',
        'processing' => 'cms.orders.processing_orders',
        'completed' => 'cms.orders.completed_orders',
        'canceled' => 'cms.orders.cancelled_orders',
    ];

    protected array $statusLabelTranslationKeys = [
        'pending' => 'cms.orders.status_labels.pending',
        'processing' => 'cms.orders.status_labels.processing',
        'completed' => 'cms.orders.status_labels.completed',
        'canceled' => 'cms.orders.status_labels.canceled',
    ];

    public function __construct(protected AdminOrderService $orderService)
    {
    }

    public function index(Request $request)
    {
        $status = $request->query('status');
        $ordersQuery = Order::query()
            ->with(['shop', 'customer'])
            ->latest();

        if (is_string($status) && in_array($status, $this->statusFilters, true)) {
            $ordersQuery->where('status', $status);
        } else {
            $status = '';
        }

        $shopId = (int) $request->query('shop', 0);
        if ($shopId > 0) {
            $ordersQuery->where('shop_id', $shopId);
        } else {
            $shopId = 0;
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $ordersQuery->where(function ($query) use ($search) {
                if (is_numeric($search)) {
                    $query->orWhere('id', (int) $search);
                }

                $query->orWhere('guest_email', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('email', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $ordersQuery->paginate(10)->withQueryString();

        $statusFilterLabels = $this->statusFilterLabels();
        $statusLabels = $this->statusLabels();
        $shops = Shop::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.orders.index', [
            'orders' => $orders,
            'statusFilters' => $statusFilterLabels,
            'currentStatus' => $status,
            'statusLabels' => $statusLabels,
            'shops' => $shops,
            'filters' => [
                'shop' => $shopId,
                'search' => $search,
            ],
        ]);
    }

    public function create()
    {
        $shops = Shop::with(['products.translation'])
            ->orderBy('name')
            ->get();

        $productOptions = $shops
            ->mapWithKeys(function (Shop $shop) {
                $products = $shop->products
                    ->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->translation?->name ?? $product->slug,
                            'sku' => $product->SKU,
                            'price' => (float) ($product->price ?? 0),
                        ];
                    })
                    ->filter(fn ($product) => filled($product['name']))
                    ->values()
                    ->toArray();

                return [$shop->id => $products];
            })
            ->toArray();

        $customers = Customer::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.orders.create', [
            'shops' => $shops,
            'customers' => $customers,
            'statusOptions' => $this->statusLabels(),
            'productOptions' => $productOptions,
        ]);
    }

    public function store(StoreOrderRequest $request)
    {
        $order = $this->orderService->create($request->validated());

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', __('cms.orders.created_success'));
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json([
            'success' => true,
            'message' => __('cms.orders.deleted_success'),
        ]);
    }

    public function show(Order $order)
    {
        $order->load([
            'shop',
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

    protected function statusFilterLabels(): array
    {
        $labels = ['' => __('cms.orders.filters.status_all')];

        foreach ($this->statusFilters as $status) {
            $translationKey = $this->statusFilterTranslationKeys[$status] ?? null;
            if ($translationKey) {
                $labels[$status] = __($translationKey);
            }
        }

        return $labels;
    }

    protected function statusLabels(): array
    {
        $labels = [];

        foreach ($this->statusFilters as $status) {
            $translationKey = $this->statusLabelTranslationKeys[$status] ?? null;
            if ($translationKey) {
                $labels[$status] = __($translationKey);
            }
        }

        return $labels;
    }
}
