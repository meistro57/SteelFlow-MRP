import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import fs from 'fs'; // <--- NEW: Needed to read the cert files

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const hmrHost = env.VITE_HMR_HOST || '10.0.0.177'; // Fallback to your IP if env is missing

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
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
        server: {
            host: '0.0.0.0',
            port: 5173,
            strictPort: true,
            cors: true,
            hmr: {
                host: hmrHost,
            },
            // --- NEW: Enable HTTPS using the same certs as Nginx ---
            https: {
                key: fs.readFileSync('/etc/nginx/certs/localhost-key.pem'),
                cert: fs.readFileSync('/etc/nginx/certs/localhost.pem'),
            },
        },
        resolve: {
            alias: {
                '@': '/resources/js',
            },
        },
    };
});
