<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('vendor')->check();
    }

    public function rules(): array
    {
        $vendor = Auth::guard('vendor')->user();

        return [
            'name' => ['sometimes', 'nullable', 'string', 'max:255', 'min:2'],
            'email' => [
                'sometimes', 'nullable',
                // 'email:rfc,dns', we can enable this later if needed
                'max:255',
                Rule::unique('vendors', 'email')->ignore($vendor->id),
            ],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20', 'min:10', 'regex:/^[\+]?[0-9\s\-\(\)]+$/'],
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'],
            'current_password' => ['required_with:password', 'string'],
            'avatar' => ['sometimes', 'nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.min' => 'Name must be at least 2 characters long.',
            'email.email' => 'Please provide a valid email address.',
            'phone.regex' => 'Please provide a valid phone number.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one digit, and one special character.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('password') && $this->filled('current_password')) {
                $vendor = Auth::guard('vendor')->user();
                if (! Hash::check($this->current_password, $vendor->password)) {
                    $validator->errors()->add('current_password', 'The current password is incorrect.');
                }
            }
        });
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        Log::warning('Vendor profile validation failed', [
            'vendor_id' => Auth::guard('vendor')->id(),
            'errors' => $validator->errors()->toArray(),
        ]);

        parent::failedValidation($validator);
    }
}
