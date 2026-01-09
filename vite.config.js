import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import fs from 'fs'; 
import path from 'path'; 

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    // Default to localhost for everyone else.
    // You will override this in your .env file.
    const hmrHost = env.VITE_HMR_HOST || 'localhost';
    // Only use HTTPS for localhost (SSL cert is only valid for localhost)
    const useHttps = hmrHost === 'localhost' && fs.existsSync('/etc/nginx/certs/localhost-key.pem') && fs.existsSync('/etc/nginx/certs/localhost.pem');
    const protocol = useHttps ? 'https' : 'http';

    return {
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                    'resources/css/filament/admin/theme.css',
                    'Modules/Inventory/resources/assets/js/app.js',
                 //   ...collectModuleAssets(),
                ],
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
           // Only use HTTPS if the certs exist (prevents crash during build)
            https: useHttps ? {
                key: fs.readFileSync('/etc/nginx/certs/localhost-key.pem'),
                cert: fs.readFileSync('/etc/nginx/certs/localhost.pem'),
            } : false,
        resolve: {
            alias: {
                '@': path.resolve(__dirname, 'resources/js'),
            },
        },
    };
});
