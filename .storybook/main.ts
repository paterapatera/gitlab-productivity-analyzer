import type { StorybookConfig } from '@storybook/react-vite';
import path from 'path';
import { fileURLToPath } from 'url';
import tailwindcss from '@tailwindcss/vite';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const config: StorybookConfig = {
  stories: [
    '../stories/**/*.mdx',
    '../stories/**/*.stories.@(js|jsx|mjs|ts|tsx)',
  ],
  addons: [
    '@chromatic-com/storybook',
    '@storybook/addon-vitest',
    '@storybook/addon-a11y',
    '@storybook/addon-docs',
    '@storybook/addon-mcp',
  ],
  framework: {
    name: '@storybook/react-vite',
    options: {},
  },
  async viteFinal(config) {
    // Storybook では不要なプラグイン（Wayfinder、Laravel Vite）を除外
    const filteredPlugins = (config.plugins || []).filter((plugin) => {
      if (typeof plugin === 'object' && plugin !== null) {
        const pluginName = (plugin as { name?: string }).name || '';
        const excludedPlugins = [
          'vite-plugin-wayfinder',
          'laravel-vite-plugin',
        ];
        return !excludedPlugins.some((excluded) =>
          pluginName.toLowerCase().includes(excluded),
        );
      }
      return true;
    });

    // パスエイリアスの設定
    if (!config.resolve) {
      config.resolve = {};
    }
    config.resolve.alias = {
      ...config.resolve.alias,
      '@': path.resolve(__dirname, '../resources/js'),
      '@inertiajs/react': path.resolve(
        __dirname,
        '../stories/mocks/inertia.tsx',
      ),
    };

    // Tailwind CSS プラグインを追加
    config.plugins = [...filteredPlugins, tailwindcss()];

    return config;
  },
};

export default config;
