<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateCouponRequest extends CouponRequest
{
    protected function codeRule(): Rule
    {
        $coupon = $this->route('coupon');

        return Rule::unique('coupons', 'code')->ignore($coupon?->getKey());
    }
}
