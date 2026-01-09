import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import fs from 'fs';
import path from 'path';
import os from 'os';

/**
 * Automatically detect the best hostname for Vite HMR based on environment
 */
function detectHmrHost(env) {
    // 1. Manual override via VITE_HMR_HOST
    if (env.VITE_HMR_HOST) {
        return env.VITE_HMR_HOST;
    }

    // 2. Try to extract from APP_URL
    if (env.APP_URL) {
        try {
            const url = new URL(env.APP_URL);
            // Only use if it's not localhost (which is the default)
            if (url.hostname !== 'localhost' && url.hostname !== '127.0.0.1') {
                return url.hostname;
            }
        } catch (e) {
            // Invalid URL, continue to next method
        }
    }

    // 3. Get the first non-internal IPv4 address
    const interfaces = os.networkInterfaces();
    for (const name of Object.keys(interfaces)) {
        for (const iface of interfaces[name]) {
            // Skip internal (loopback) and non-IPv4 addresses
            if (iface.family === 'IPv4' && !iface.internal) {
                console.log(`🔍 Auto-detected HMR host: ${iface.address} (from ${name})`);
                return iface.address;
            }
        }
    }

    // 4. Fall back to localhost
    console.log('⚠️  Could not auto-detect network address, using localhost');
    return 'localhost';
}

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    // Automatically detect the best HMR host for this environment
    const hmrHost = detectHmrHost(env);

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
        server: {
            host: '0.0.0.0', // Listen on all interfaces for remote access
            port: 5173,
            strictPort: true,
            hmr: {
                host: hmrHost,
                protocol: protocol,
            },
        },
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
