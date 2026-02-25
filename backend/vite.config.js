import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    // server: {
    //     host: true,          // WAJIB
    //     port: 5173,
    //     hmr: {
    //         host: '172.16.10.124', // samakan dengan IP yang kamu akses
    //     },
    // },
})