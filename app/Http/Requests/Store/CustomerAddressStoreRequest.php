<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class CustomerAddressStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.required', ['attribute' => __('cms.customers.address_name')]),
            'phone.required' => __('validation.required', ['attribute' => __('cms.customers.phone')]),
            'address.required' => __('validation.required', ['attribute' => __('cms.customers.address')]),
            'city.required' => __('validation.required', ['attribute' => __('cms.customers.city')]),
            'postal_code.required' => __('validation.required', ['attribute' => __('cms.customers.postal_code')]),
            'country.required' => __('validation.required', ['attribute' => __('cms.customers.country')]),
        ];
    }
}
