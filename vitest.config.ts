import { storybookTest } from '@storybook/addon-vitest/vitest-plugin';
import react from '@vitejs/plugin-react';
import { playwright } from '@vitest/browser-playwright';
import { fileURLToPath } from 'node:url';
import path from 'path';
import { defineConfig } from 'vitest/config';
const dirname =
    typeof __dirname !== 'undefined'
        ? __dirname
        : path.dirname(fileURLToPath(import.meta.url));

// More info at: https://storybook.js.org/docs/next/writing-tests/integrations/vitest-addon
export default defineConfig({
    plugins: [react()],
    test: {
        globals: true,
        environment: 'jsdom',
        setupFiles: ['./resources/js/test/setup.ts'],
        include: ['resources/js/**/*.test.{ts,tsx}', 'resources/js/**/*.spec.{ts,tsx}'],
        projects: [
            {
                test: {
                    name: 'unit',
                    include: ['resources/js/**/*.test.{ts,tsx}', 'resources/js/**/*.spec.{ts,tsx}'],
                },
            },
            {
                extends: true,
                plugins: [
                    // The plugin will run tests for the stories defined in your Storybook config
                    // See options at: https://storybook.js.org/docs/next/writing-tests/integrations/vitest-addon#storybooktest
                    storybookTest({
                        configDir: path.join(dirname, '.storybook'),
                    }),
                ],
                test: {
                    name: 'storybook',
                    browser: {
                        enabled: true,
                        headless: true,
                        provider: playwright({}),
                        instances: [
                            {
                                browser: 'chromium',
                            },
                        ],
                    },
                    setupFiles: ['.storybook/vitest.setup.ts'],
                },
            },
        ],
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
});
