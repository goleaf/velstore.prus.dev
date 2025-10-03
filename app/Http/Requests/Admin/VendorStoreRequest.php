<?php

namespace App\Http\Requests\Admin;

use App\Models\Shop;
use App\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VendorStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->whenFilled('name', fn ($value) => trim((string) $value)),
            'email' => $this->whenFilled('email', fn ($value) => strtolower(trim((string) $value))),
            'phone' => $this->whenFilled('phone', fn ($value) => trim((string) $value) ?: null),
            'status' => $this->whenFilled('status', fn ($value) => strtolower(trim((string) $value))),
            'shop_name' => $this->whenFilled('shop_name', fn ($value) => trim((string) $value)),
            'shop_slug' => $this->whenFilled('shop_slug', fn ($value) => Str::slug((string) $value) ?: null),
            'shop_description' => $this->whenFilled('shop_description', fn ($value) => trim((string) $value) ?: null),
            'shop_status' => $this->whenFilled('shop_status', fn ($value) => strtolower(trim((string) $value))),
        ]);

        if (! $this->filled('shop_slug') && $this->filled('shop_name')) {
            $this->merge([
                'shop_slug' => Str::slug((string) $this->input('shop_name')) ?: null,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('vendors', 'email')],
            'password' => [
                'required',
                'confirmed',
                'string',
                'min:8',
                'regex:/[^\w\s]/',
            ],
            'password_confirmation' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s\-]+$/'],
            'status' => ['required', Rule::in(Vendor::STATUSES)],
            'shop_name' => ['required', 'string', 'max:255'],
            'shop_slug' => ['nullable', 'string', 'max:255', Rule::unique('shops', 'slug')],
            'shop_description' => ['nullable', 'string', 'max:500'],
            'shop_status' => ['required', Rule::in(Shop::STATUSES)],
        ];
    }

    public function messages(): array
    {
        return [
            'password.confirmed' => __('validation.confirmed', ['attribute' => __('cms.vendors.password')]),
            'password.regex' => __('cms.vendors.password_symbol_validation'),
            'phone.regex' => __('validation.regex', ['attribute' => __('cms.vendors.phone_optional')]),
            'shop_slug.unique' => __('validation.unique', ['attribute' => __('cms.vendors.shop_slug')]),
        ];
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        unset($validated['password_confirmation']);

        return $validated;
    }
}
