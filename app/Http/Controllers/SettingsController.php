<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SettingsController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        $settings = Setting::getMany([
            'app_name',
            'app_tagline',
            'logo_path',
            'logo_width',
            'logo_height',
            'font_family_display',
            'font_family_body',
            'primary_color',
            'secondary_color',
            'dark_mode_enabled',
        ]);

        return view('settings.index', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:100',
            'app_tagline' => 'nullable|string|max:255',
            'logo_width' => 'nullable|integer|min:20|max:200',
            'logo_height' => 'nullable|integer|min:20|max:200',
            'font_family_display' => 'nullable|string|max:100',
            'font_family_body' => 'nullable|string|max:100',
            'primary_color' => 'nullable|string|max:20',
            'secondary_color' => 'nullable|string|max:20',
        ]);

        try {
            // App Name & Tagline
            Setting::set('app_name', $request->app_name);
            Setting::set('app_tagline', $request->app_tagline ?? '');

            // Logo
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoName = 'logo-' . time() . '.' . $logo->getClientOriginalExtension();
                $logo->move(public_path('images'), $logoName);
                Setting::set('logo_path', 'images/' . $logoName);
            }

            // Logo dimensions
            Setting::set('logo_width', $request->logo_width ?? 40);
            Setting::set('logo_height', $request->logo_height ?? 40);

            // Font families
            Setting::set('font_family_display', $request->font_family_display ?? 'Sora');
            Setting::set('font_family_body', $request->font_family_body ?? 'Inter');

            // Colors
            Setting::set('primary_color', $request->primary_color ?? '#9333ea');
            Setting::set('secondary_color', $request->secondary_color ?? '#a855f7');

            // Dark mode
            Setting::set('dark_mode_enabled', $request->has('dark_mode_enabled') ? '1' : '0');

            // Clear config cache
            cache()->forget('settings');

            return redirect()->back()->with('success', 'Pengaturan berhasil disimpan!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }

    /**
     * Reset to defaults
     */
    public function reset()
    {
        $defaults = [
            'app_name' => 'Passnet',
            'app_tagline' => 'ISP Management',
            'logo_path' => 'images/logo-passnet.png',
            'logo_width' => 40,
            'logo_height' => 40,
            'font_family_display' => 'Sora',
            'font_family_body' => 'Inter',
            'primary_color' => '#9333ea',
            'secondary_color' => '#a855f7',
            'dark_mode_enabled' => '1',
        ];

        foreach ($defaults as $key => $value) {
            Setting::set($key, $value);
        }

        cache()->forget('settings');

        return redirect()->back()->with('success', 'Pengaturan berhasil direset ke default!');
    }
}
