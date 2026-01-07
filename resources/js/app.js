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
