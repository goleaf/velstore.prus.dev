<?php

namespace App\Services\Store;

use App\Models\Coupon;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function applyCoupon(string $code): array
    {
        $code = trim($code);

        $coupon = Coupon::query()
            ->whereRaw('LOWER(code) = ?', [mb_strtolower($code)])
            ->first();

        if (! $coupon) {
            return $this->failureResponse(__('cms.coupons.errors.invalid_code'));
        }

        $cartSubtotal = $this->calculateCartSubtotal();

        if ($cartSubtotal <= 0) {
            return $this->failureResponse(__('cms.coupons.errors.empty_cart'));
        }

        if ($coupon->isExpired()) {
            return $this->failureResponse(__('cms.coupons.errors.expired'));
        }

        if ($coupon->hasReachedUsageLimit()) {
            return $this->failureResponse(__('cms.coupons.errors.usage_limit_reached'));
        }

        if (! $coupon->meetsMinimumSpend($cartSubtotal)) {
            return $this->failureResponse(
                __('cms.coupons.errors.minimum_spend_requirement', [
                    'amount' => number_format((float) $coupon->minimum_spend, 2),
                ])
            );
        }

        Session::put('cart_coupon', [
            'id' => $coupon->getKey(),
        ]);

        $summary = $this->summarizeCart();

        return array_merge([
            'success' => true,
            'message' => 'Coupon applied successfully!',
        ], $summary);
    }

    public function getCartTotalWithDiscount($total)
    {
        $coupon = $this->resolveCouponFromSession();

        if (! $coupon) {
            return $total;
        }

        if ($coupon->isExpired() || $coupon->hasReachedUsageLimit()) {
            Session::forget('cart_coupon');

            return $total;
        }

        if (! $coupon->meetsMinimumSpend((float) $total)) {
            return $total;
        }

        return max(0, $total - $this->calculateDiscountAmount($coupon, (float) $total));
    }

    public function removeCoupon()
    {
        Session::forget('cart_coupon');
    }

    public function summarizeCart(): array
    {
        $subtotal = $this->calculateCartSubtotal();
        $coupon = $this->resolveCouponFromSession();
        $couponError = null;
        $couponErrorKey = null;
        $couponErrorParams = [];
        $couponErrorMeta = [];

        if ($coupon) {
            if ($coupon->isExpired()) {
                Session::forget('cart_coupon');
                $couponErrorKey = 'cms.coupons.errors.expired';
                $coupon = null;
            } elseif ($coupon->hasReachedUsageLimit()) {
                Session::forget('cart_coupon');
                $couponErrorKey = 'cms.coupons.errors.usage_limit_reached';
                $coupon = null;
            } elseif (! $coupon->meetsMinimumSpend($subtotal)) {
                $difference = max(0, (float) $coupon->minimum_spend - $subtotal);

                $couponErrorKey = 'cms.coupons.errors.minimum_spend_short';
                $couponErrorParams = ['amount' => number_format($difference, 2)];
                $couponErrorMeta = ['amount' => $difference];
            }
        }

        if ($couponErrorKey) {
            $couponError = __($couponErrorKey, $couponErrorParams);
        }

        $discountAmount = $coupon ? $this->calculateDiscountAmount($coupon, $subtotal) : 0.0;

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'final_total' => max(0, $subtotal - $discountAmount),
            'coupon' => $coupon ? $this->presentCoupon($coupon) : null,
            'coupon_error' => $couponError,
            'coupon_error_key' => $couponErrorKey,
            'coupon_error_params' => $couponErrorParams,
            'coupon_error_meta' => $couponErrorMeta,
        ];
    }

    protected function calculateCartSubtotal(): float
    {
        return collect(Session::get('cart', []))
            ->reduce(function ($carry, $item) {
                $price = (float) ($item['price'] ?? 0);
                $quantity = (int) ($item['quantity'] ?? 0);

                return $carry + ($price * max(0, $quantity));
            }, 0.0);
    }

    protected function calculateDiscountAmount(Coupon $coupon, float $total): float
    {
        if ($coupon->type === 'percentage') {
            return round($total * ((float) $coupon->discount / 100), 2);
        }

        return min($total, (float) $coupon->discount);
    }

    protected function resolveCouponFromSession(): ?Coupon
    {
        $sessionCoupon = Session::get('cart_coupon');

        if (! is_array($sessionCoupon) || empty($sessionCoupon['id'])) {
            return null;
        }

        return Coupon::find($sessionCoupon['id']);
    }

    protected function presentCoupon(Coupon $coupon): array
    {
        return [
            'id' => $coupon->getKey(),
            'code' => $coupon->code,
            'type' => $coupon->type,
            'discount' => (float) $coupon->discount,
            'minimum_spend' => $coupon->minimum_spend !== null ? (float) $coupon->minimum_spend : null,
            'usage_limit' => $coupon->usage_limit,
            'usage_count' => $coupon->usage_count,
            'expires_at' => $coupon->expires_at?->toIso8601String(),
        ];
    }

    protected function failureResponse(string $message): array
    {
        return array_merge([
            'success' => false,
            'message' => $message,
        ], $this->summarizeCart());
    }
}
