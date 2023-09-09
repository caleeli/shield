import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { createVuePlugin as vue } from 'vite-plugin-vue2';

export default defineConfig(({ command, mode }) => {

    return {
        resolve: {
            alias: {
                vue: 'vue/dist/vue.esm.js',
            },
        },
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                    'resources/js/login.js',
                ],
                refresh: true,
            }),
            vue({
                jsx: false, // Optional, if you want to use JSX with Vue 2
            }),
        ],
    }
});
