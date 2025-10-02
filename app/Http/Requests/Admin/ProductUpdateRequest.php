<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
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
        return [
            // Translations
            'translations' => 'required|array',
            'translations.*.name' => 'required|string|max:255',
            'translations.*.description' => 'nullable|string',
            // Product details
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'vendor_id' => 'required|exists:vendors,id',
            // Variants
            'variants' => 'required|array|min:1',
            'variants.*.name' => 'required|string|max:255',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.discount_price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.SKU' => 'required|string|max:100',
            'variants.*.barcode' => 'nullable|string|max:100',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.dimensions' => 'nullable|string|max:255',
            'variants.*.size_id' => 'nullable|exists:attribute_values,id',
            'variants.*.color_id' => 'nullable|exists:attribute_values,id',
            // Images
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'integer|exists:product_images,id',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Translations
            'translations.required' => 'Product translations are required.',
            'translations.*.name.required' => 'Product name is required for all languages.',
            'translations.*.name.max' => 'Product name must not exceed 255 characters.',
            // Product details
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category is invalid.',
            'brand_id.exists' => 'The selected brand is invalid.',
            'vendor_id.required' => 'Please select a vendor.',
            'vendor_id.exists' => 'The selected vendor is invalid.',
            // Variants
            'variants.required' => 'At least one product variant is required.',
            'variants.min' => 'At least one product variant is required.',
            'variants.*.name.required' => 'Variant name is required.',
            'variants.*.name.max' => 'Variant name must not exceed 255 characters.',
            'variants.*.price.required' => 'Variant price is required.',
            'variants.*.price.numeric' => 'Variant price must be a valid number.',
            'variants.*.price.min' => 'Variant price must be at least 0.',
            'variants.*.discount_price.numeric' => 'Discount price must be a valid number.',
            'variants.*.discount_price.min' => 'Discount price must be at least 0.',
            'variants.*.stock.required' => 'Stock quantity is required.',
            'variants.*.stock.integer' => 'Stock must be a whole number.',
            'variants.*.stock.min' => 'Stock must be at least 0.',
            'variants.*.SKU.required' => 'SKU is required.',
            'variants.*.SKU.max' => 'SKU must not exceed 100 characters.',
            'variants.*.barcode.max' => 'Barcode must not exceed 100 characters.',
            'variants.*.weight.min' => 'Weight must be at least 0.',
            'variants.*.dimensions.max' => 'Dimensions must not exceed 255 characters.',
            'variants.*.size_id.exists' => 'The selected size is invalid.',
            'variants.*.color_id.exists' => 'The selected color is invalid.',
            // Images
            'images.*.image' => 'Uploaded file must be an image.',
            'images.*.mimes' => 'Image must be a file of type: jpeg, png, jpg, gif, webp.',
            'images.*.max' => 'Image size must not exceed 10MB.',
            'remove_images.*.integer' => 'Invalid image ID.',
            'remove_images.*.exists' => 'Selected image does not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'translations.*.name' => 'product name',
            'translations.*.description' => 'product description',
            'variants.*.name' => 'variant name',
            'variants.*.price' => 'variant price',
            'variants.*.discount_price' => 'discount price',
            'variants.*.stock' => 'stock quantity',
            'variants.*.SKU' => 'SKU',
            'variants.*.barcode' => 'barcode',
            'variants.*.weight' => 'weight',
            'variants.*.dimensions' => 'dimensions',
            'variants.*.size_id' => 'size',
            'variants.*.color_id' => 'color',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Remove empty variant entries
        if ($this->has('variants')) {
            $variants = array_filter($this->variants, function ($variant) {
                return !empty($variant['name']) || !empty($variant['price']);
            });
            $this->merge(['variants' => array_values($variants)]);
        }

        // Remove empty translation entries
        if ($this->has('translations')) {
            $translations = array_filter($this->translations, function ($translation) {
                return !empty($translation['name']);
            });
            $this->merge(['translations' => $translations]);
        }
    }
}
