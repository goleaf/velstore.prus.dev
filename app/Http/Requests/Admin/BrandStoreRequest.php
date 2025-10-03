<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BrandStoreRequest extends FormRequest
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
            'logo_url' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10000',
            'translations' => 'required|array',
        ];

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
            'logo_url.file' => __('validation.file', ['attribute' => 'brand logo']),
            'logo_url.image' => __('validation.image', ['attribute' => 'brand logo']),
            'logo_url.mimes' => __('validation.mimes', ['attribute' => 'brand logo', 'values' => 'jpeg, png, jpg, gif, svg, webp']),
            'logo_url.max' => __('validation.max.file', ['attribute' => 'brand logo', 'max' => '10000']),
            'translations.required' => __('validation.required', ['attribute' => 'translations']),
            'translations.array' => __('validation.array', ['attribute' => 'translations']),
            'translations.*.name.required' => __('validation.required', ['attribute' => 'brand name']),
            'translations.*.name.max' => __('validation.max.string', ['attribute' => 'brand name', 'max' => '255']),
            'translations.*.description.required' => __('validation.required', ['attribute' => 'brand description']),
            'translations.*.description.min' => __('validation.min.string', ['attribute' => 'brand description', 'min' => '5']),
        ];
    }
}
