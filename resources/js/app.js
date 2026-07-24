import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

createInertiaApp({
    title: (title) => title ? `${title} — Inventory System` : 'Inventory System',
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin);
        app.use(ZiggyVue);

        // Global components
        app.component('Head', Head);
        app.component('Link', Link);

        // Global helper: format currency Rupiah
        app.config.globalProperties.$currency = (value) => {
            if (value === null || value === undefined) return 'Rp 0';
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            }).format(value);
        };

        // Global helper: format number
        app.config.globalProperties.$number = (value) => {
            if (value === null || value === undefined) return '0';
            return new Intl.NumberFormat('id-ID').format(value);
        };

        // Global helper: format date
        app.config.globalProperties.$date = (value) => {
            if (!value) return '-';
            return new Date(value).toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
        };

        // Global helper: format datetime
        app.config.globalProperties.$datetime = (value) => {
            if (!value) return '-';
            return new Date(value).toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        };

        app.mount(el);
    },
    progress: {
        color: '#3b82f6',
        showSpinner: true,
    },
});
