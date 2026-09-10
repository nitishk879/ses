import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import livewire from '@defstudio/vite-livewire-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/css/admin.css',
                'resources/js/app.js',
                'resources/js/main.js',
            ],
            refresh: false, // False for livewire
        }),
        livewire({  // <-- add livewire plugin
            refresh: ['resources/sass/app.scss'],  // <-- will refresh css (tailwind ) as well
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                quietDeps: true,
            },
        },
    },
    resolve: {
        alias: {
            // vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
});
