<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SiteSettingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $hexColorRule = ['nullable', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'];

        return [
            'site_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'support_hours' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'logo' => ['nullable', 'string', 'max:255'],
            'favicon' => ['nullable', 'string', 'max:255'],
            'primary_color' => $hexColorRule,
            'secondary_color' => $hexColorRule,
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'maintenance_mode' => ['sometimes', 'boolean'],
            'maintenance_message' => ['nullable', 'string', 'max:500', 'required_if:maintenance_mode,1'],
            'footer_text' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'primary_color.regex' => __('The primary color must be a valid hexadecimal color code.'),
            'secondary_color.regex' => __('The secondary color must be a valid hexadecimal color code.'),
        ];
    }
}
