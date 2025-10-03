<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VendorStoreRequest;
use App\Models\Shop;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class VendorController extends Controller
{
    public function index()
    {
        $statusCounts = Vendor::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $shopStatusCounts = Shop::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $stats = [
            'vendors' => [
                'total' => $statusCounts->sum(),
                'active' => (int) ($statusCounts['active'] ?? 0),
                'inactive' => (int) ($statusCounts['inactive'] ?? 0),
                'banned' => (int) ($statusCounts['banned'] ?? 0),
            ],
            'shops' => [
                'total' => $shopStatusCounts->sum(),
                'active' => (int) ($shopStatusCounts['active'] ?? 0),
            ],
        ];

        $statusOptions = $this->statusOptions();

        return view('admin.vendors.index', compact('stats', 'statusOptions'));
    }

    public function getVendorData(Request $request)
    {
        $status = strtolower((string) $request->get('status'));

        $vendors = Vendor::query()
            ->select(['id', 'name', 'email', 'phone', 'status', 'created_at'])
            ->withCount('shops')
            ->with(['shops:id,vendor_id,name,status'])
            ->when(
                in_array($status, Vendor::STATUSES, true),
                fn ($query) => $query->where('status', $status)
            )
            ->latest('id');

        $statusOptions = $this->statusOptions();
        $shopStatusLabels = $this->shopStatusLabels();

        return DataTables::of($vendors)
            ->editColumn('phone', fn ($vendor) => $vendor->phone ?: '—')
            ->addColumn('registered_at', function ($vendor) {
                if (! $vendor->created_at) {
                    return '—';
                }

                return $vendor->created_at
                    ->timezone(config('app.timezone'))
                    ->format('M j, Y');
            })
            ->editColumn('status', function ($vendor) use ($statusOptions) {
                $status = strtolower((string) $vendor->status);

                $label = $statusOptions[$status] ?? __('cms.vendors.status_unknown');
                $badgeClass = match ($status) {
                    'active' => 'badge-success',
                    'inactive' => 'badge-warning',
                    'banned' => 'badge-danger',
                    default => 'badge-secondary',
                };

                return '<span class="badge ' . $badgeClass . '">' . e($label) . '</span>';
            })
            ->addColumn('shops', function ($vendor) use ($shopStatusLabels) {
                if (! $vendor->shops_count) {
                    return '—';
                }

                $shopSummary = $vendor->shops
                    ->map(function ($shop) use ($shopStatusLabels) {
                        $status = strtolower((string) $shop->status);
                        $label = $shopStatusLabels[$status] ?? ucfirst($status);

                        return '<span class="d-block text-sm">' . e($shop->name) . ' <span class="text-muted">(' . e($label) . ')</span></span>';
                    })
                    ->implode('');

                return '<div class="space-y-1">' . $shopSummary . '</div>';
            })
            ->addColumn('action', function ($vendor) {
                $deleteLabel = e(__('cms.vendors.delete_button'));
                $viewLabel = e(__('cms.vendors.view_button'));

                return <<<HTML
                    <div class="flex flex-col gap-2">
                        <button type="button"
                                class="btn btn-outline-primary btn-sm w-full"
                                data-action="view-vendor"
                                data-vendor-id="{$vendor->id}"
                                title="{$viewLabel}">
                            {$viewLabel}
                        </button>
                        <button type="button"
                                class="btn btn-outline-danger btn-sm w-full"
                                data-action="delete-vendor"
                                data-vendor-id="{$vendor->id}"
                                title="{$deleteLabel}">
                            {$deleteLabel}
                        </button>
                    </div>
                HTML;
            })
            ->rawColumns(['status', 'shops', 'action'])
            ->make(true);
    }

    public function create()
    {
        $statusOptions = $this->statusOptions();
        $shopStatusOptions = $this->shopStatusOptions();

        return view('admin.vendors.create', compact('statusOptions', 'shopStatusOptions'));
    }

    public function store(VendorStoreRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $vendor = Vendor::create(Arr::only($validated, ['name', 'email', 'password', 'phone', 'status']));

            $shopSlug = $validated['shop_slug'] ?? Shop::generateUniqueSlug($validated['shop_name']);
            if (Shop::where('slug', $shopSlug)->exists()) {
                $shopSlug = Shop::generateUniqueSlug($shopSlug);
            }

            Shop::create([
                'vendor_id' => $vendor->id,
                'name' => $validated['shop_name'],
                'slug' => $shopSlug,
                'description' => $validated['shop_description'] ?? null,
                'status' => $validated['shop_status'],
            ]);
        });

        return redirect()->route('admin.vendors.index')
            ->with('success', __('cms.vendors.success_create'));
    }

    public function show(Vendor $vendor)
    {
        $vendor->load(['shops' => function ($query) {
            $query->orderBy('name')->withCount('products');
        }])->loadCount('shops');

        $totalProducts = $vendor->shops->sum('products_count');
        $activeShopCount = $vendor->shops->where('status', 'active')->count();

        return view('admin.vendors.show', compact('vendor', 'totalProducts', 'activeShopCount'));
    }

    public function destroy($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->delete();

        return response()->json([
            'success' => true,
            'message' => __('cms.vendors.success_delete'),
        ]);
    }

    private function statusOptions(): array
    {
        return collect(Vendor::STATUSES)
            ->mapWithKeys(fn ($status) => [
                $status => __('cms.vendors.status_' . $status),
            ])
            ->all();
    }

    private function shopStatusOptions(): array
    {
        return collect(Shop::STATUSES)
            ->mapWithKeys(fn ($status) => [
                $status => __('cms.vendors.shop_status_label_' . $status),
            ])
            ->all();
    }

    private function shopStatusLabels(): array
    {
        return $this->shopStatusOptions();
    }
}
