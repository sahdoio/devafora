<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            :root {
                /* Theme Colors */
                --color-primary: {{ \App\Models\Setting::get('color_primary', '#3b82f6') }};
                --color-secondary: {{ \App\Models\Setting::get('color_secondary', '#8b5cf6') }};
                --color-accent: {{ \App\Models\Setting::get('color_accent', '#06b6d4') }};
                --color-success: {{ \App\Models\Setting::get('color_success', '#10b981') }};
                --color-warning: {{ \App\Models\Setting::get('color_warning', '#f59e0b') }};
                --color-danger: {{ \App\Models\Setting::get('color_danger', '#ef4444') }};

                /* Background Colors */
                --color-bg-primary: {{ \App\Models\Setting::get('color_bg_primary', '#0f172a') }};
                --color-bg-secondary: {{ \App\Models\Setting::get('color_bg_secondary', '#1e293b') }};
                --color-bg-card: {{ \App\Models\Setting::get('color_bg_card', '#334155') }};

                /* Text Colors */
                --color-text-primary: {{ \App\Models\Setting::get('color_text_primary', '#ffffff') }};
                --color-text-secondary: {{ \App\Models\Setting::get('color_text_secondary', '#cbd5e1') }};
                --color-text-muted: {{ \App\Models\Setting::get('color_text_muted', '#94a3b8') }};

                /* Border Colors */
                --color-border: {{ \App\Models\Setting::get('color_border', '#475569') }};
                --color-border-light: {{ \App\Models\Setting::get('color_border_light', '#64748b') }};
            }

            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/images/logo_icon.png" type="image/png">
        <link rel="apple-touch-icon" href="/images/logo_icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
