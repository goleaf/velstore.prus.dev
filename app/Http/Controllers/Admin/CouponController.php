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
    protected array $statusFilters = ['active', 'expired'];

    public function index(Request $request)
    {
        $status = $request->query('status');
        $now = Carbon::now();

        $couponsQuery = Coupon::query()->latest();

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
        } else {
            $status = '';
        }

        $coupons = $couponsQuery->paginate(10)->withQueryString();

        $stats = $this->getCouponStats($now);

        $statusFilterLabels = [
            '' => __('cms.coupons.filters.all'),
            'active' => __('cms.coupons.filters.active'),
            'expired' => __('cms.coupons.filters.expired'),
        ];

        return view('admin.coupons.index', [
            'coupons' => $coupons,
            'stats' => $stats,
            'statusFilters' => $statusFilterLabels,
            'currentStatus' => $status,
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

        return [
            'total' => Coupon::count(),
            'active' => $activeCount,
            'expired' => $expiredCount,
        ];
    }
}
