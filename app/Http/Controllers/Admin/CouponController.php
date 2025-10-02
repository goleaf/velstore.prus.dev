<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CouponController extends Controller
{
    public function index()
    {
        return view('admin.coupons.index');
    }

    public function getData(Request $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $coupons = Coupon::query();

        return DataTables::of($coupons)
            ->editColumn('discount', function (Coupon $coupon) {
                $value = rtrim(rtrim(number_format($coupon->discount, 2), '0'), '.');

                return $coupon->type === 'percentage'
                    ? $value.'%'
                    : $value;
            })
            ->editColumn('type', function (Coupon $coupon) {
                return __('cms.coupons.type_labels.'.$coupon->type);
            })
            ->editColumn('expires_at', function (Coupon $coupon) {
                return $coupon->expires_at
                    ? $coupon->expires_at->format('Y-m-d H:i')
                    : __('cms.coupons.no_expiry');
            })
            ->addColumn('action', function (Coupon $coupon) {
                $editUrl = route('admin.coupons.edit', $coupon->id);
                $deleteAction = "deleteCoupon({$coupon->id})";

                return '<span class="border border-edit dt-trash rounded-3 d-inline-block">'
                    .'<a href="'.$editUrl.'" class=""><i class="bi bi-pencil-fill pencil-edit-color"></i></a></span> '
                    .'<span class="border border-danger dt-trash rounded-3 d-inline-block" onclick="'.$deleteAction.'">'
                    .'<i class="bi bi-trash-fill text-danger"></i></span>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', Rule::unique('coupons', 'code')],
            'discount' => ['required', 'numeric', 'min:0'],
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'expires_at' => ['nullable', 'date'],
        ]);

        if ($validated['type'] === 'percentage' && $validated['discount'] > 100) {
            return back()->withErrors([
                'discount' => __('cms.coupons.errors.percentage_limit'),
            ])->withInput();
        }

        $validated['expires_at'] = $this->parseExpiry($request->input('expires_at'));

        Coupon::create($validated);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', __('cms.coupons.created'));
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', Rule::unique('coupons', 'code')->ignore($coupon->id)],
            'discount' => ['required', 'numeric', 'min:0'],
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'expires_at' => ['nullable', 'date'],
        ]);

        if ($validated['type'] === 'percentage' && $validated['discount'] > 100) {
            return back()->withErrors([
                'discount' => __('cms.coupons.errors.percentage_limit'),
            ])->withInput();
        }

        $validated['expires_at'] = $this->parseExpiry($request->input('expires_at'));

        $coupon->update($validated);

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

    private function parseExpiry(?string $expiresAt): ?Carbon
    {
        if (! $expiresAt) {
            return null;
        }

        try {
            return Carbon::parse($expiresAt);
        } catch (\Throwable $throwable) {
            Log::warning('Invalid coupon expiry provided', [
                'value' => $expiresAt,
                'error' => $throwable->getMessage(),
            ]);

            return null;
        }
    }
}
