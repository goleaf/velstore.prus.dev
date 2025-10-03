<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductReviewDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:approved,pending'],
            'rating_min' => ['nullable', 'integer', 'min:1', 'max:5'],
            'rating_max' => ['nullable', 'integer', 'min:1', 'max:5'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'product_name' => ['nullable', 'string'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'has_review' => ['nullable', 'boolean'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'has_review' => $this->boolean('has_review'),
            'product_name' => $this->filled('product_name') ? trim((string) $this->input('product_name')) : null,
        ]);
    }

    public function filters(): array
    {
        return $this->safe()->only([
            'status',
            'rating_min',
            'rating_max',
            'product_id',
            'product_name',
            'customer_id',
            'has_review',
            'date_from',
            'date_to',
        ]);
    }
}

