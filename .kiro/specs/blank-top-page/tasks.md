# Implementation Plan

- 1. ExamplePage コンポーネントを作成する
- [x] 1.1 ExamplePage コンポーネントを実装する
  - トップページで表示される真っ白な空白画面コンポーネントを作成する
  - 背景色を白（`#FFFFFF`）に設定する
  - 既存のウェルカムページのコンテンツ（ヘッダー、ナビゲーション、メインコンテンツ、フッターなど）を一切表示しない
  - 最小限の HTML 構造のみを含む（div 要素と基本的なスタイリング）
  - Inertia.js の Head コンポーネントを使用してページタイトルを設定する
  - _Requirements: 1.1, 1.2, 1.3_

- [x] 1.2 ダークモード対応を実装する
  - ダークモードが有効な場合でも白背景を維持する実装を追加する
  - または適切なダークモード対応を実装する
  - _Requirements: 1.4_

- 2. トップページのルート定義を更新する
- [x] 2.1 HomeRoute を更新して ExamplePage をレンダリングする
  - `routes/web.php` の `home` ルート定義を更新する
  - 既存の `welcome` ページコンポーネントへの参照を `example` に変更する
  - ルート名 `home` を維持する
  - _Requirements: 2.3_

- 3. 既存のウェルカムページコンポーネントを削除する
- [x] 3.1 (P) welcome.tsx ファイルを削除する
  - `resources/js/pages/welcome.tsx` ファイルを削除する
  - 他のルートで参照されていないことを確認する
  - _Requirements: 2.1, 2.2_

- 4. Dashboard ページとルート定義を削除する
- [x] 4.1 (P) Dashboard ページコンポーネントを削除する
  - `resources/js/pages/dashboard.tsx` ファイルを削除する
  - _Requirements: 3.1, 3.2_

- [x] 4.2 (P) Dashboard ルート定義を削除する
  - `routes/web.php` から `dashboard` ルート定義を削除する
  - ルート名 `dashboard` の定義を削除する
  - _Requirements: 3.3_

- 5. Dashboard への参照を削除する
- [x] 5.1 (P) AppSidebar から Dashboard 参照を削除する
  - `resources/js/components/app-sidebar.tsx` を更新する
  - `mainNavItems` 配列から Dashboard ナビゲーションアイテムを削除する
  - サイドバーヘッダーのロゴリンクから Dashboard への参照を削除する（代替リンク先を決定する必要がある）
  - `@/routes` からの `dashboard` インポートを削除する
  - _Requirements: 3.4, 3.5_

- [x] 5.2 (P) AppHeader から Dashboard 参照を削除する
  - `resources/js/components/app-header.tsx` を更新する
  - `mainNavItems` 配列から Dashboard ナビゲーションアイテムを削除する
  - `@/routes` からの `dashboard` インポートを削除する
  - _Requirements: 3.4, 3.5_

- 6. 統合テストと検証を実行する
- [x] 6.1 トップページの動作を確認する
  - ルートパス（`/`）にアクセスして ExamplePage が正しくレンダリングされることを確認する
  - 白背景の空白画面が表示されることを確認する
  - ダークモードが有効な場合でも適切に表示されることを確認する
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 2.3_

- [x] 6.2 Dashboard 削除の確認を行う
  - Dashboard ルートにアクセスして 404 エラーが返されることを確認する
  - AppSidebar と AppHeader から Dashboard 参照が削除されていることを確認する
  - 型定義やインポート文が正しく削除されていることを確認する
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_
