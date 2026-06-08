import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',   // Add this line back!
                'resources/sass/app.scss', // Keep your Sass file
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                // 1. Force Vite to use the modern engine so it respects our mute list
                api: 'modern-compiler', 
                // 2. Mute all warnings coming from third-party folders (like Bootstrap)
                quietDeps: true,
                // 3. Mute specific warnings in your own files
                silenceDeprecations: ['import', 'if-function', 'global-builtin'],
            },
        },
    },
});