import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import fs from 'fs';
import path from 'path';
import os from 'os';

/**
 * Collect module asset entry points
 */
function collectModuleAssets() {
    const modulesPath = path.resolve(__dirname, 'Modules');
    const assets = [];

    if (fs.existsSync(modulesPath)) {
        const modules = fs.readdirSync(modulesPath);
        for (const module of modules) {
            const modulePath = path.join(modulesPath, module);
            if (fs.statSync(modulePath).isDirectory()) {
                // Check for js/app.js
                const jsPath = path.join(modulePath, 'resources/assets/js/app.js');
                if (fs.existsSync(jsPath)) {
                    assets.push(`Modules/${module}/resources/assets/js/app.js`);
                }

                // Check for css/app.css
                const cssPath = path.join(modulePath, 'resources/assets/css/app.css');
                if (fs.existsSync(cssPath)) {
                    assets.push(`Modules/${module}/resources/assets/css/app.css`);
                }
            }
        }
    }

    return assets;
}

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

    // Only terminate TLS directly on Vite's own listener for the pure-localhost
    // case (SSL cert is only valid for localhost). In every other case Nginx
    // sits in front, terminates TLS, and proxies to Vite over plain HTTP
    // internally, so Vite's own listener stays HTTP.
    const useHttps = hmrHost === 'localhost' && fs.existsSync('/etc/nginx/certs/localhost-key.pem') && fs.existsSync('/etc/nginx/certs/localhost.pem');

    // The HMR *client* protocol (what the browser connects with) must match
    // the scheme the page was actually loaded over, since Nginx is the one
    // terminating TLS externally - this is independent of Vite's own listener.
    let publicIsHttps = false;
    try {
        publicIsHttps = new URL(env.APP_URL || 'http://localhost').protocol === 'https:';
    } catch {
        // Invalid APP_URL, fall back to non-TLS.
    }
    const protocol = publicIsHttps ? 'wss' : 'ws';

    return {
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                    'resources/css/filament/admin/theme.css',
                    ...collectModuleAssets(),
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
            allowedHosts: true, // Allow all hosts for development
            origin: env.APP_URL || 'http://localhost', // Force asset URLs to use the main domain
            hmr: {
                host: hmrHost,
                protocol: protocol,
                clientPort: protocol === 'wss' ? 443 : 80, // Use Nginx port for HMR client
                path: 'vite-hmr', // Distinct path so Nginx can proxy the HMR websocket specifically
            },
            cors: {
                origin: '*', // Allow all origins for development
                credentials: true,
            },
            headers: {
                // Allow Private Network Access from public domains
                'Access-Control-Allow-Private-Network': 'true',
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
                '@Modules': path.resolve(__dirname, 'Modules'),
            },
        },
    };
});
