import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import fs from 'fs';

export default defineConfig(({ command, mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    return {
        plugins: [
            laravel({
                input: ['resources/css/app.scss', 'resources/js/app.js'],
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        server: {
            host: true,
            https: {
                key: fs.readFileSync(env.VITE_SERVER_HTTPS_KEY),
                cert: fs.readFileSync(env.VITE_SERVER_HTTPS_CERT),
            },
            hmr: {
                host: env.VITE_SERVER_HMR_HOST,
            },
        },
    };
});
