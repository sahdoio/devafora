import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
            // In the split docker setup Vite runs in the node container, which has
            // no PHP. The php container generates these files (see `make wayfinder`),
            // so the plugin's own invocation is turned into a no-op there via
            // WAYFINDER_COMMAND=true. Local (non-docker) dev keeps the default.
            command: process.env.WAYFINDER_COMMAND ?? 'php artisan wayfinder:generate',
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
