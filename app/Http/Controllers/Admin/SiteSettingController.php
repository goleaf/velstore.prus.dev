<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteSettingUpdateRequest;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function index(): View
    {
        $settings = $this->getSiteSettings();

        return view('admin.site-settings.index', [
            'settings' => $settings,
        ]);
    }

    public function edit(): View
    {
        $settings = $this->getSiteSettings();

        return view('admin.site-settings.edit', [
            'settings' => $settings,
        ]);
    }

    public function update(SiteSettingUpdateRequest $request): RedirectResponse
    {
        $settings = $this->getSiteSettings();
        $payload = $request->validated();

        $settings->fill($payload);
        $settings->maintenance_mode = $request->boolean('maintenance_mode');
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
