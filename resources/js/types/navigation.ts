/**
 * Wayfinder ルート関数の型定義
 * Wayfinder で生成されたルート関数は、呼び出すと { url: string; method: string } を返す
 */
export type RouteFunction = () => { url: string; method: string };

/**
 * メニュー項目の型定義
 */
export interface MenuItem {
    /**
     * メニュー項目のラベルテキスト
     */
    label: string;

    /**
     * Wayfinder で生成されたルート関数
     * 呼び出すと { url: string; method: string } を返す
     */
    route: RouteFunction;

    /**
     * ルートのパス名（文字列）
     * アクティブ状態の判定に使用（例: '/projects', '/commits/collect'）
     */
    path: string;
}
