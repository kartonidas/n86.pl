import { fileURLToPath, URL } from 'node:url';

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import fs from 'fs';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
    server: {
        host: true,
        https: {
            key: fs.readFileSync("../cert/privkey1.pem"),
            cert: fs.readFileSync("../cert/cert1.pem"),
        },
        hmr: {
            host: 'estate.netextend.pl',
        },
    },
});
