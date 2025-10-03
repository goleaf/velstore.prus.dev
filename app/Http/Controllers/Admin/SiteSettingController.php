<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
            'contact_email' => ['nullable', 'email'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'footer_text' => ['nullable', 'string'],
        ]);

        $settings = $this->getSiteSettings();
        $settings->fill($validated);
        $settings->save();

        return redirect()
            ->route('admin.site-settings.index')
            ->with('success', 'Site settings updated successfully!');
    }

    private function getSiteSettings(): SiteSetting
    {
        return SiteSetting::firstOrNew();
    }
}
