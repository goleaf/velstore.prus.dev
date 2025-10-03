<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function index(): View
    {
        return view('admin.site-settings.index', [
            'settings' => $this->getSiteSettings(),
        ]);
    }

    public function edit(): View
    {
        return view('admin.site-settings.edit', [
            'settings' => $this->getSiteSettings(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'top_bar_message' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'footer_text' => ['nullable', 'string'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,ico', 'max:512'],
        ]);

        $settings = $this->getSiteSettings();
        $settings->fill($validated);

        if ($request->hasFile('logo')) {
            $this->deleteExistingFile($settings->logo);
            $settings->logo = $request->file('logo')->store('site-settings', 'public');
        }

        if ($request->hasFile('favicon')) {
            $this->deleteExistingFile($settings->favicon);
            $settings->favicon = $request->file('favicon')->store('site-settings', 'public');
        }

        $settings->save();

        Cache::forget('site_settings');

        return redirect()
            ->route('admin.site-settings.index')
            ->with('success', 'Site settings updated successfully!');
    }

    private function getSiteSettings(): SiteSetting
    {
        return SiteSetting::firstOrNew();
    }

    private function deleteExistingFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (Str::startsWith($path, ['http://', 'https://', 'assets/', 'images/', '/'])) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
