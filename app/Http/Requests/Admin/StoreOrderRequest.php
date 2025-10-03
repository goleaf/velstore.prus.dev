<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'exists:shops,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'guest_email' => ['nullable', 'email', 'max:255', 'required_without:customer_id'],
            'status' => ['required', Rule::in(['pending', 'processing', 'completed', 'canceled'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'shipping' => ['nullable', 'array'],
            'shipping.name' => ['nullable', 'string', 'max:255'],
            'shipping.phone' => ['nullable', 'string', 'max:50'],
            'shipping.address' => ['nullable', 'string', 'max:255'],
            'shipping.city' => ['nullable', 'string', 'max:120'],
            'shipping.postal_code' => ['nullable', 'string', 'max:30'],
            'shipping.country' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'guest_email.required_without' => __('cms.orders.validation.guest_or_customer_required'),
            'items.required' => __('cms.orders.validation.items_required'),
            'items.min' => __('cms.orders.validation.items_required'),
            'items.*.product_id.required' => __('cms.orders.validation.product_required'),
            'items.*.product_id.exists' => __('cms.orders.validation.items_invalid'),
            'items.*.quantity.min' => __('cms.orders.validation.quantity_min'),
        ];
    }
}
