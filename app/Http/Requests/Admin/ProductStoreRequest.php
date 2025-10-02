<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $defaultLang = config('app.locale');

        $rules = [
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'vendor_id' => 'required|exists:vendors,id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'variants' => 'required|array|min:1',
            'variants.*.name' => 'required|string|max:255',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.discount_price' => 'nullable|numeric|min:0|lte:variants.*.price',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.SKU' => 'required|string|max:255',
            'variants.*.barcode' => 'nullable|string|max:255',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.dimensions' => 'nullable|string|max:255',
            'variants.*.language_code' => 'nullable|string|size:2',
            'variants.*.size_id' => 'nullable|exists:attribute_values,id',
            'variants.*.color_id' => 'nullable|exists:attribute_values,id',
        ];

        // Add translation validation rules
        foreach ($this->input('translations', []) as $lang => $data) {
            $rules["translations.$lang.name"] = 'required|string|max:255';
            $rules["translations.$lang.description"] = 'required|string|min:5';
        }

        return $rules;
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.required' => __('validation.required', ['attribute' => 'category']),
            'category_id.exists' => __('validation.exists', ['attribute' => 'category']),
            'brand_id.exists' => __('validation.exists', ['attribute' => 'brand']),
            'vendor_id.required' => __('validation.required', ['attribute' => 'vendor']),
            'vendor_id.exists' => __('validation.exists', ['attribute' => 'vendor']),
            'images.*.image' => __('validation.image', ['attribute' => 'image']),
            'images.*.mimes' => __('validation.mimes', ['attribute' => 'image', 'values' => 'jpeg, png, jpg, gif, webp']),
            'images.*.max' => __('validation.max.file', ['attribute' => 'image', 'max' => '2048']),
            'variants.required' => __('validation.required', ['attribute' => 'product variants']),
            'variants.min' => __('validation.min.array', ['attribute' => 'variants', 'min' => '1']),
            'variants.*.name.required' => __('validation.required', ['attribute' => 'variant name']),
            'variants.*.name.max' => __('validation.max.string', ['attribute' => 'variant name', 'max' => '255']),
            'variants.*.price.required' => __('validation.required', ['attribute' => 'variant price']),
            'variants.*.price.numeric' => __('validation.numeric', ['attribute' => 'variant price']),
            'variants.*.price.min' => __('validation.min.numeric', ['attribute' => 'variant price', 'min' => '0']),
            'variants.*.discount_price.numeric' => __('validation.numeric', ['attribute' => 'discount price']),
            'variants.*.discount_price.min' => __('validation.min.numeric', ['attribute' => 'discount price', 'min' => '0']),
            'variants.*.discount_price.lte' => __('validation.lte.numeric', ['attribute' => 'discount price', 'value' => 'price']),
            'variants.*.stock.required' => __('validation.required', ['attribute' => 'stock']),
            'variants.*.stock.integer' => __('validation.integer', ['attribute' => 'stock']),
            'variants.*.stock.min' => __('validation.min.numeric', ['attribute' => 'stock', 'min' => '0']),
            'variants.*.SKU.required' => __('validation.required', ['attribute' => 'SKU']),
            'variants.*.SKU.max' => __('validation.max.string', ['attribute' => 'SKU', 'max' => '255']),
            'variants.*.barcode.max' => __('validation.max.string', ['attribute' => 'barcode', 'max' => '255']),
            'variants.*.weight.numeric' => __('validation.numeric', ['attribute' => 'weight']),
            'variants.*.weight.min' => __('validation.min.numeric', ['attribute' => 'weight', 'min' => '0']),
            'variants.*.dimensions.max' => __('validation.max.string', ['attribute' => 'dimensions', 'max' => '255']),
            'variants.*.language_code.size' => __('validation.size.string', ['attribute' => 'language code', 'size' => '2']),
            'variants.*.size_id.exists' => __('validation.exists', ['attribute' => 'size']),
            'variants.*.color_id.exists' => __('validation.exists', ['attribute' => 'color']),
            'translations.*.name.required' => __('validation.required', ['attribute' => 'product name']),
            'translations.*.name.max' => __('validation.max.string', ['attribute' => 'product name', 'max' => '255']),
            'translations.*.description.required' => __('validation.required', ['attribute' => 'product description']),
            'translations.*.description.min' => __('validation.min.string', ['attribute' => 'product description', 'min' => '5']),
        ];
    }
}
