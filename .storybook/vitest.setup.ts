import * as a11yAddonAnnotations from '@storybook/addon-a11y/preview';
import { setProjectAnnotations } from '@storybook/react-vite';
import * as projectAnnotations from './preview';

/**
 * Storybook のプロジェクトアノテーションを設定します。
 * これにより、Vitest でストーリーをテストする際に正しい設定が適用されます。
 *
 * @see https://storybook.js.org/docs/api/portable-stories/portable-stories-vitest#setprojectannotations
 */
setProjectAnnotations([a11yAddonAnnotations, projectAnnotations]);