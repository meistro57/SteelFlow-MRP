import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import fs from 'fs'; 
//import collectModuleAssets from './vite-module-loader.js'; 

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
        server: {
            host: '0.0.0.0',
            port: 5173,
            strictPort: true,
            cors: true,
            origin: `${protocol}://${hmrHost}:5173`,
            hmr: {
                host: hmrHost,
            },
           // Only use HTTPS if the certs exist (prevents crash during build)
            https: useHttps ? {
                key: fs.readFileSync('/etc/nginx/certs/localhost-key.pem'),
                cert: fs.readFileSync('/etc/nginx/certs/localhost.pem'),
            } : false,
        },
        resolve: {
            alias: {
                '@': '/resources/js',
            },
        },
    };
});
