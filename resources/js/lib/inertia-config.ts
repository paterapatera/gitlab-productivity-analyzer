/**
 * Inertia.js アプリケーションの共通設定
 */

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

/**
 * ページタイトルを生成する関数
 */
export function createPageTitle(title?: string): string {
    return title ? `${title} - ${appName}` : appName;
}
