<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VendorStoreRequest;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class VendorController extends Controller
{
    public function index()
    {
        $statusCounts = Vendor::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $stats = [
            'total' => $statusCounts->sum(),
            'active' => (int) ($statusCounts['active'] ?? 0),
            'inactive' => (int) ($statusCounts['inactive'] ?? 0),
            'banned' => (int) ($statusCounts['banned'] ?? 0),
        ];

        $statusOptions = $this->statusOptions();

        return view('admin.vendors.index', compact('stats', 'statusOptions'));
    }

    public function getVendorData(Request $request)
    {
        $status = strtolower((string) $request->get('status'));

        $vendors = Vendor::query()
            ->select(['id', 'name', 'email', 'phone', 'status', 'created_at'])
            ->when(
                in_array($status, Vendor::STATUSES, true),
                fn ($query) => $query->where('status', $status)
            )
            ->latest('id');

        $statusOptions = $this->statusOptions();

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
            ->addColumn('action', function ($vendor) {
                $deleteLabel = e(__('cms.vendors.delete_button'));

                return <<<HTML
                    <div class="flex flex-col gap-2">
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
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $statusOptions = $this->statusOptions();

        return view('admin.vendors.create', compact('statusOptions'));
    }

    public function store(VendorStoreRequest $request)
    {
        Vendor::create($request->validated());

        return redirect()->route('admin.vendors.index')
            ->with('success', __('cms.vendors.success_create'));
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
}
