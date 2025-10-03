<?php

namespace App\Http\Requests\Admin;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

abstract class CouponRequest extends FormRequest
{
    protected ?Carbon $parsedExpiresAt = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255', $this->codeRule()],
            'discount' => ['required', 'numeric', 'min:0'],
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'expires_at' => ['nullable', 'date'],
        ];
    }

    abstract protected function codeRule(): Rule;

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('type');
            $discount = $this->input('discount');

            if ($type === 'percentage' && is_numeric($discount) && (float) $discount > 100) {
                $validator->errors()->add('discount', __('cms.coupons.errors.percentage_limit'));
            }
        });
    }

    protected function passedValidation(): void
    {
        $expiresAt = $this->input('expires_at');

        if (! $expiresAt) {
            $this->parsedExpiresAt = null;

            return;
        }

        try {
            $this->parsedExpiresAt = Carbon::parse($expiresAt);
        } catch (\Throwable $throwable) {
            Log::warning('Invalid coupon expiry provided', [
                'value' => $expiresAt,
                'error' => $throwable->getMessage(),
            ]);

            $this->parsedExpiresAt = null;
        }
    }

    public function validatedData(): array
    {
        $data = $this->validated();
        $data['expires_at'] = $this->parsedExpiresAt;

        return $data;
    }
}
