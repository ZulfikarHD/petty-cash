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

    // Dev Enable this, Prod Comment This
    server: {
        host: '0.0.0.0', // Listen on all network interfaces
        port: 5173,
        hmr: {
            host: '10.30.11.65', // Use the server's IP for HMR
            port: 5173,
        },
        cors: {
            origin: true, // Allow all origins during development
        },
    },
});
