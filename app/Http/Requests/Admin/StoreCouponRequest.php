<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class StoreCouponRequest extends CouponRequest
{
    protected function codeRule(): Rule
    {
        return Rule::unique('coupons', 'code');
    }
}
