import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { route as ziggyRoute } from 'ziggy-js';
import '../css/app.css';

declare global {
    function route(
        name: string,
        params?: Record<string, any>,
        absolute?: boolean,
    ): string;
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        (window as any).route = (name: string, params?: any, absolute?: boolean) =>
            ziggyRoute(name, params, absolute, props.initialPage.props.ziggy);

        app.use(plugin).mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
