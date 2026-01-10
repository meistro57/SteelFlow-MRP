import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import { ZiggyVue } from 'ziggy-js'; // <--- ADDED THIS

const appName = import.meta.env.VITE_APP_NAME || 'SteelFlow MRP';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const parts = name.split('::');
        if (parts.length > 1) {
            const module = parts[0];
            const page = parts[1];
            return resolvePageComponent(`../../Modules/${module}/resources/assets/js/Pages/${page}.vue`, import.meta.glob('../../Modules/**/*.vue'));
        }
        return resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'));
    },
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();

        // Initialize theme and layout density from user settings
        const userSettings = props.initialPage.props.auth?.user?.settings || {};

        // Apply theme
        const theme = userSettings.theme || 'light';
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else if (theme === 'system') {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (prefersDark) {
                document.documentElement.classList.add('dark');
            }
        }

        // Apply layout density
        const density = userSettings.layout_density || 'comfortable';
        document.documentElement.setAttribute('data-density', density);

        // Apply sidebar state
        const sidebarCollapsed = userSettings.sidebar_collapsed || false;
        if (sidebarCollapsed) {
            document.documentElement.setAttribute('data-sidebar', 'collapsed');
        }

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue) // <--- ADDED THIS
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
