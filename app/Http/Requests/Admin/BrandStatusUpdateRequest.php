<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BrandStatusUpdateRequest extends FormRequest
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
            'id' => 'required|exists:brands,id',
            'status' => 'required|string|in:active,inactive,discontinued',
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
            'id.required' => __('validation.required', ['attribute' => 'brand ID']),
            'id.exists' => __('validation.exists', ['attribute' => 'brand']),
            'status.required' => __('validation.required', ['attribute' => 'status']),
            'status.string' => __('validation.string', ['attribute' => 'status']),
            'status.in' => __('validation.in', ['attribute' => 'status']),
        ];
    }
}
