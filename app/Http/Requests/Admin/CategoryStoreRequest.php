<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CategoryStoreRequest extends FormRequest
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
        $rules = [
            'translations' => 'required|array',
            'parent_category_id' => 'nullable|integer|exists:categories,id',
            'status' => 'nullable|boolean',
        ];

        foreach ($this->input('translations', []) as $lang => $data) {
            $rules["translations.$lang.name"] = 'required|string|max:255';
            $rules["translations.$lang.description"] = 'required|string|min:5';
            $rules["translations.$lang.image"] = 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048';
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
            'translations.required' => __('validation.required', ['attribute' => 'translations']),
            'translations.array' => __('validation.array', ['attribute' => 'translations']),
            'translations.*.name.required' => __('validation.required', ['attribute' => 'category name']),
            'translations.*.name.max' => __('validation.max.string', ['attribute' => 'category name', 'max' => '255']),
            'translations.*.description.required' => __('validation.required', ['attribute' => 'category description']),
            'translations.*.description.min' => __('validation.min.string', ['attribute' => 'category description', 'min' => '5']),
            'translations.*.image.required' => __('validation.required', ['attribute' => 'category image']),
            'translations.*.image.image' => __('validation.image', ['attribute' => 'category image']),
            'translations.*.image.mimes' => __('validation.mimes', ['attribute' => 'category image', 'values' => 'jpeg, png, jpg, gif, webp']),
            'translations.*.image.max' => __('validation.max.file', ['attribute' => 'category image', 'max' => '2048']),
            'parent_category_id.exists' => __('validation.exists', ['attribute' => 'parent category']),
            'status.boolean' => __('validation.boolean', ['attribute' => 'status']),
        ];
    }
}
