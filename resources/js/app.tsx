import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { AppLayout } from './layouts/AppLayout';
import { createPageTitle } from './lib/inertia-config';

createInertiaApp({
    title: createPageTitle,
    resolve: (name) => {
        const pages = import.meta.glob('./pages/**/*.tsx', { eager: true });
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        const page = pages[`./pages/${name}.tsx`] as any;
        page.default.layout =
            page.default.layout ||
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            ((page: any) => <AppLayout children={page} />);
        return page;
    },
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <StrictMode>
                <App {...props} />
            </StrictMode>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});
