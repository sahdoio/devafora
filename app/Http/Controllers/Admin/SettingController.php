<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    /**
     * Display the theme settings page.
     */
    public function theme()
    {
        $settings = Setting::where('group', 'theme')
            ->orderBy('key')
            ->get();

        return Inertia::render('Admin/Settings/Theme', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update theme settings.
     */
    public function updateTheme(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required|string',
        ]);

        foreach ($validated['settings'] as $setting) {
            Setting::set($setting['key'], $setting['value'], 'color', 'theme');
        }

        Setting::clearCache();

        return redirect()->route('admin.settings.theme')
            ->with('success', 'Theme colors updated successfully.');
    }
}
