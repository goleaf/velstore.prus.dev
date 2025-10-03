<?php

namespace App\Http\Requests\Admin;

use App\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
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
        ]);
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
        ];
    }

    public function messages(): array
    {
        return [
            'password.confirmed' => __('validation.confirmed', ['attribute' => __('cms.vendors.password')]),
            'password.regex' => __('cms.vendors.password_symbol_validation'),
            'phone.regex' => __('validation.regex', ['attribute' => __('cms.vendors.phone_optional')]),
        ];
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        unset($validated['password_confirmation']);

        return $validated;
    }
}
