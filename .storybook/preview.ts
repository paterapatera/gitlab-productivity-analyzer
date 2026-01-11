import type { Preview } from '@storybook/react-vite';
import '../resources/css/app.css';

const preview: Preview = {
    parameters: {
        controls: {
            matchers: {
                color: /(background|color)$/i,
                date: /Date$/i,
            },
        },
        a11y: {
            // 'todo' - テスト UI でのみ a11y 違反を表示
            // 'error' - CI で a11y 違反をエラーとして扱う
            // 'off' - a11y チェックをスキップ
            test: 'todo',
        },
    },
};

export default preview;
