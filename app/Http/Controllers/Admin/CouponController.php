<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CouponController extends Controller
{
    protected array $statusFilters = ['active', 'expired', 'expiring_soon'];
    protected array $typeFilters = ['percentage', 'fixed'];
    protected array $usageFilters = ['limited', 'unlimited'];

    public function index(Request $request)
    {
        $status = $request->query('status');
        $type = $request->query('type');
        $usage = $request->query('usage');
        $search = trim((string) $request->query('search', ''));
        $now = Carbon::now();

        $couponsQuery = Coupon::query()->latest();

        if ($search !== '') {
            $couponsQuery->where(function ($query) use ($search) {
                $query->where('code', 'like', "%{$search}%");

                if (is_numeric($search)) {
                    $query->orWhere('id', (int) $search);
                }
            });
        }

        if (is_string($type) && in_array($type, $this->typeFilters, true)) {
            $couponsQuery->where('type', $type);
        } else {
            $type = '';
        }

        if (is_string($usage) && in_array($usage, $this->usageFilters, true)) {
            if ($usage === 'limited') {
                $couponsQuery->whereNotNull('usage_limit');
            }

            if ($usage === 'unlimited') {
                $couponsQuery->whereNull('usage_limit');
            }
        } else {
            $usage = '';
        }

        if (is_string($status) && in_array($status, $this->statusFilters, true)) {
            if ($status === 'active') {
                $couponsQuery->where(function ($query) use ($now) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>=', $now);
                });
            }

            if ($status === 'expired') {
                $couponsQuery->whereNotNull('expires_at')
                    ->where('expires_at', '<', $now);
            }

            if ($status === 'expiring_soon') {
                $couponsQuery->whereNotNull('expires_at')
                    ->whereBetween('expires_at', [$now, $now->copy()->addDays(7)]);
            }
        } else {
            $status = '';
        }

        $coupons = $couponsQuery->paginate(10)->withQueryString();

        $stats = $this->getCouponStats($now);

        $statusFilterLabels = [
            '' => __('cms.coupons.filters.all'),
            'active' => __('cms.coupons.filters.active'),
            'expired' => __('cms.coupons.filters.expired'),
            'expiring_soon' => __('cms.coupons.filters.expiring_soon'),
        ];

        $typeFilterLabels = [
            '' => __('cms.coupons.filters.type.all'),
            'percentage' => __('cms.coupons.filters.type.percentage'),
            'fixed' => __('cms.coupons.filters.type.fixed'),
        ];

        $usageFilterLabels = [
            '' => __('cms.coupons.filters.usage.all'),
            'limited' => __('cms.coupons.filters.usage.limited'),
            'unlimited' => __('cms.coupons.filters.usage.unlimited'),
        ];

        return view('admin.coupons.index', [
            'coupons' => $coupons,
            'stats' => $stats,
            'statusFilters' => $statusFilterLabels,
            'currentStatus' => $status,
            'typeFilters' => $typeFilterLabels,
            'currentType' => $type,
            'usageFilters' => $usageFilterLabels,
            'currentUsage' => $usage,
            'searchTerm' => $search,
        ]);
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(StoreCouponRequest $request)
    {
        Coupon::create($request->validatedData());

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', __('cms.coupons.created'));
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon)
    {
        $coupon->update($request->validatedData());

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', __('cms.coupons.updated'));
    }

    public function destroy(Coupon $coupon)
    {
        try {
            $coupon->delete();

            return response()->json([
                'success' => true,
                'message' => __('cms.coupons.deleted'),
                'stats' => $this->getCouponStats(),
            ]);
        } catch (\Throwable $throwable) {
            Log::error('Failed to delete coupon', [
                'coupon_id' => $coupon->id,
                'error' => $throwable->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('cms.coupons.errors.delete_failed'),
            ], 500);
        }
    }

    private function getCouponStats(?Carbon $reference = null): array
    {
        $now = $reference ?? Carbon::now();
        $expiringSoonThreshold = $now->copy()->addDays(7);

        $activeCount = Coupon::query()
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', $now);
            })
            ->count();

        $expiredCount = Coupon::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->count();

        $expiringSoonCount = Coupon::query()
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [$now, $expiringSoonThreshold])
            ->count();

        $unlimitedCount = Coupon::query()
            ->whereNull('usage_limit')
            ->count();

        return [
            'total' => Coupon::count(),
            'active' => $activeCount,
            'expired' => $expiredCount,
            'expiring_soon' => $expiringSoonCount,
            'unlimited' => $unlimitedCount,
        ];
    }
}
