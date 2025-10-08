<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Theme Colors
            ['key' => 'color_primary', 'value' => '#3b82f6', 'type' => 'color', 'group' => 'theme'],
            ['key' => 'color_secondary', 'value' => '#8b5cf6', 'type' => 'color', 'group' => 'theme'],
            ['key' => 'color_accent', 'value' => '#06b6d4', 'type' => 'color', 'group' => 'theme'],
            ['key' => 'color_success', 'value' => '#10b981', 'type' => 'color', 'group' => 'theme'],
            ['key' => 'color_warning', 'value' => '#f59e0b', 'type' => 'color', 'group' => 'theme'],
            ['key' => 'color_danger', 'value' => '#ef4444', 'type' => 'color', 'group' => 'theme'],

            // Background Colors
            ['key' => 'color_bg_primary', 'value' => '#0f172a', 'type' => 'color', 'group' => 'theme'],
            ['key' => 'color_bg_secondary', 'value' => '#1e293b', 'type' => 'color', 'group' => 'theme'],
            ['key' => 'color_bg_card', 'value' => '#334155', 'type' => 'color', 'group' => 'theme'],

            // Text Colors
            ['key' => 'color_text_primary', 'value' => '#ffffff', 'type' => 'color', 'group' => 'theme'],
            ['key' => 'color_text_secondary', 'value' => '#cbd5e1', 'type' => 'color', 'group' => 'theme'],
            ['key' => 'color_text_muted', 'value' => '#94a3b8', 'type' => 'color', 'group' => 'theme'],

            // Border Colors
            ['key' => 'color_border', 'value' => '#475569', 'type' => 'color', 'group' => 'theme'],
            ['key' => 'color_border_light', 'value' => '#64748b', 'type' => 'color', 'group' => 'theme'],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
