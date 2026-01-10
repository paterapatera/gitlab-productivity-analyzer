# Design Document

## Overview

本機能は、アプリケーションのトップページ（ルートパス `/`）を既存のウェルカムページから真っ白な空白画面に変更します。また、トップページから遷移できなくなったページ（Dashboard）とその関連コンポーネント、ルート定義を削除してコードベースを整理します。

**Purpose**: トップページを最小限の空白画面に変更し、不要なページとコードを削除することで、コードベースを簡素化します。

**Users**: エンドユーザーはトップページにアクセスした際に、装飾のない空白画面を確認します。開発者は不要なコードが削除されたクリーンなコードベースを維持できます。

**Impact**: 既存のウェルカムページコンポーネント（`resources/js/pages/welcome.tsx`）を削除または置き換え、Dashboard ページ（`resources/js/pages/dashboard.tsx`）とその関連参照を削除します。ルート定義（`routes/web.php`）を更新して、トップページが新しい空白ページコンポーネントをレンダリングするように変更します。

### Goals
- トップページ（`/`）を真っ白な空白画面として表示する
- 既存のウェルカムページコンポーネントを削除または置き換える
- Dashboard ページとその関連参照を完全に削除する
- ルート定義を更新して新しい空白ページをレンダリングする
- 削除対象のページへの参照をすべて削除または更新する

### Non-Goals
- 他のページ（settings など）への影響は対象外
- データベーススキーマの変更は不要
- 認証やミドルウェアの変更は不要
- 既存のレイアウトコンポーネント（AppLayout など）の変更は不要

## Architecture

### Existing Architecture Analysis

現在のアーキテクチャは Laravel + React (Inertia.js) の SPA 構造です：

- **バックエンド**: Laravel 12 がルート定義（`routes/web.php`）で Inertia.js を使用して React コンポーネントをレンダリング
- **フロントエンド**: React 19 + TypeScript でページコンポーネント（`resources/js/pages/`）を実装
- **ルーティング**: Laravel Wayfinder が型安全なルート生成関数を提供（`@/routes` からインポート）

既存のパターン：
- ページコンポーネントは `resources/js/pages/` に配置
- ルート定義は `routes/web.php` で Inertia::render() を使用
- 型安全なルート生成は `@/routes` からインポート

### Architecture Pattern & Boundary Map

本機能は既存アーキテクチャの範囲内で実装され、新しいパターンや境界は導入しません。既存の Inertia.js ページコンポーネントパターンに従います。

**Architecture Integration**:
- 選択パターン: 既存の Inertia.js ページコンポーネントパターン（変更なし）
- ドメイン境界: フロントエンドページコンポーネントとバックエンドルート定義の境界を維持
- 既存パターンの維持: Inertia.js のページレンダリング、型安全なルート生成、コンポーネント構造
- 新規コンポーネントの理由: 空白ページコンポーネント（`ExamplePage`）を新規作成してトップページを実装
- Steering 準拠: 既存のディレクトリ構造、命名規則、型安全性の原則を維持

### Technology Stack

| Layer | Choice / Version | Role in Feature | Notes |
|-------|------------------|-----------------|-------|
| Frontend | React 19, TypeScript 5.7 | 空白ページコンポーネントの実装 | 既存スタック、変更なし |
| Frontend | Inertia.js 2.x | ページコンポーネントのレンダリング | 既存スタック、変更なし |
| Backend | Laravel 12 | ルート定義の更新 | 既存スタック、変更なし |
| Build Tool | Vite 7 | ビルドと開発サーバー | 既存スタック、変更なし |
| Styling | Tailwind CSS 4 | 空白ページのスタイリング | 既存スタック、変更なし |

## Requirements Traceability

| Requirement | Summary | Components | Interfaces | Flows |
|-------------|---------|------------|------------|-------|
| 1.1 | ルートパスで白背景の空白画面を表示 | ExamplePage | - | ページレンダリング |
| 1.2 | ウェルカムページコンテンツを非表示 | ExamplePage | - | ページレンダリング |
| 1.3 | 最小限の HTML 構造 | ExamplePage | - | ページレンダリング |
| 1.4 | ダークモード対応 | ExamplePage | - | ページレンダリング |
| 2.1 | ウェルカムページコンポーネントの削除 | - | - | ファイル削除 |
| 2.2 | 他のルート参照のエラー回避 | - | - | 参照確認 |
| 2.3 | ルート定義の更新 | HomeRoute | - | ルート定義 |
| 3.1 | Dashboard ページの削除 | - | - | ファイル削除 |
| 3.2 | Dashboard コンポーネントの削除 | - | - | ファイル削除 |
| 3.3 | Dashboard ルート定義の削除 | DashboardRoute | - | ルート定義削除 |
| 3.4 | Dashboard 参照の削除/更新 | AppSidebar, AppHeader | - | 参照削除 |
| 3.5 | 型定義やインポートの削除 | - | - | コードクリーンアップ |

## Components and Interfaces

### Frontend / Pages

#### ExamplePage

| Field | Detail |
|-------|--------|
| Intent | トップページ（`/`）で表示される真っ白な空白画面コンポーネント |
| Requirements | 1.1, 1.2, 1.3, 1.4 |
| Owner / Reviewers | - |

**Responsibilities & Constraints**
- 背景色が白（`#FFFFFF`）の空白画面を表示する
- 既存のウェルカムページのコンテンツ（ヘッダー、ナビゲーション、メインコンテンツ、フッターなど）を一切表示しない
- 最小限の HTML 構造のみを含む（div 要素と基本的なスタイリング）
- ダークモードが有効な場合でも白背景を維持する（または適切なダークモード対応を実装する）

**Dependencies**
- Inbound: Inertia.js Head コンポーネント — ページタイトル設定（P1）
- Outbound: なし
- External: Tailwind CSS — スタイリング（P0）

**Contracts**: State [ ]

##### State Management
- State model: ステートレスコンポーネント（props なし）
- Persistence & consistency: 不要（静的表示のみ）
- Concurrency strategy: 不要

**Implementation Notes**
- Integration: Inertia.js のページコンポーネントとして実装。`routes/web.php` の `home` ルートからレンダリングされる
- Validation: 不要（静的コンテンツのみ）
- Risks: ダークモード対応の実装方法を決定する必要がある（白背景を強制するか、ダークモードに適応するか）

### Backend / Routes

#### HomeRoute

| Field | Detail |
|-------|--------|
| Intent | トップページ（`/`）のルート定義を更新して ExamplePage をレンダリングする |
| Requirements | 2.3 |
| Owner / Reviewers | - |

**Responsibilities & Constraints**
- ルートパス（`/`）で ExamplePage コンポーネントをレンダリングする
- 既存の `welcome` ページコンポーネントへの参照を削除する
- ルート名 `home` を維持する

**Dependencies**
- Inbound: Laravel Route — ルート定義（P0）
- Outbound: Inertia::render() — ページコンポーネントのレンダリング（P0）
- External: なし

**Contracts**: API [ ]

##### API Contract
| Method | Endpoint | Request | Response | Errors |
|--------|----------|---------|----------|--------|
| GET | / | - | Inertia Page (ExamplePage) | 500 (サーバーエラー) |

**Implementation Notes**
- Integration: `routes/web.php` の既存の `home` ルート定義を更新
- Validation: 不要
- Risks: なし

#### DashboardRoute (削除対象)

| Field | Detail |
|-------|--------|
| Intent | Dashboard ページのルート定義を削除する |
| Requirements | 3.3 |
| Owner / Reviewers | - |

**Responsibilities & Constraints**
- `routes/web.php` から `dashboard` ルート定義を削除する
- ルート名 `dashboard` の定義を削除する

**Dependencies**
- Inbound: なし（削除対象）
- Outbound: なし（削除対象）
- External: なし

**Implementation Notes**
- Integration: `routes/web.php` から Dashboard ルート定義を削除
- Validation: 削除前に他のコンポーネントからの参照を確認
- Risks: 他のコンポーネントが `dashboard()` ルート関数を参照している場合、参照エラーが発生する可能性がある

### Frontend / Components

#### AppSidebar (更新)

| Field | Detail |
|-------|--------|
| Intent | サイドバーコンポーネントから Dashboard への参照を削除する |
| Requirements | 3.4 |
| Owner / Reviewers | - |

**Responsibilities & Constraints**
- `mainNavItems` 配列から Dashboard ナビゲーションアイテムを削除する
- サイドバーヘッダーのロゴリンクから Dashboard への参照を削除する（代替リンク先を決定する必要がある）

**Dependencies**
- Inbound: `@/routes` — ルート生成関数（P0、削除対象）
- Outbound: NavMain, Sidebar コンポーネント — UI レンダリング（P0）
- External: なし

**Implementation Notes**
- Integration: `resources/js/components/app-sidebar.tsx` を更新して Dashboard 参照を削除
- Validation: 削除後、サイドバーが空のナビゲーションアイテムリストにならないように確認
- Risks: ロゴリンクの代替先を決定する必要がある（トップページへのリンク、またはリンクを削除）

#### AppHeader (更新)

| Field | Detail |
|-------|--------|
| Intent | ヘッダーコンポーネントから Dashboard への参照を削除する |
| Requirements | 3.4 |
| Owner / Reviewers | - |

**Responsibilities & Constraints**
- `mainNavItems` 配列から Dashboard ナビゲーションアイテムを削除する

**Dependencies**
- Inbound: `@/routes` — ルート生成関数（P0、削除対象）
- Outbound: NavigationMenu コンポーネント — UI レンダリング（P0）
- External: なし

**Implementation Notes**
- Integration: `resources/js/components/app-header.tsx` を更新して Dashboard 参照を削除
- Validation: 削除後、ヘッダーが空のナビゲーションアイテムリストにならないように確認
- Risks: なし

## Data Models

本機能はデータモデルの変更を伴いません。静的ページコンポーネントの追加と削除のみです。

## Error Handling

### Error Strategy

本機能は静的ページコンポーネントの変更のみのため、特別なエラーハンドリングは不要です。既存の Inertia.js と Laravel のエラーハンドリングメカニズムに依存します。

### Error Categories and Responses

**User Errors** (4xx): 該当なし（静的ページのみ）

**System Errors** (5xx): 
- ページコンポーネントが見つからない場合: Inertia.js が自動的にエラーを処理
- ルート定義エラー: Laravel が 500 エラーを返す

**Business Logic Errors** (422): 該当なし

### Monitoring

既存の Laravel ログとエラートラッキングメカニズムに依存します。特別な監視は不要です。

## Testing Strategy

### Unit Tests
- ExamplePage コンポーネントが正しくレンダリングされることを確認
- ExamplePage コンポーネントが白背景を表示することを確認
- ExamplePage コンポーネントが最小限の HTML 構造のみを含むことを確認

### Integration Tests
- ルートパス（`/`）が ExamplePage をレンダリングすることを確認
- Dashboard ルートが削除され、404 エラーを返すことを確認
- AppSidebar と AppHeader から Dashboard 参照が削除されていることを確認

### E2E/UI Tests
- トップページ（`/`）にアクセスした際に空白画面が表示されることを確認
- ダークモードが有効な場合でも適切に表示されることを確認
- Dashboard ページに直接アクセスした際に 404 エラーが返されることを確認

## Optional Sections

### Security Considerations

本機能は認証や認可を伴わない静的ページの変更のみのため、特別なセキュリティ考慮事項はありません。既存のセキュリティメカニズムを維持します。

### Performance & Scalability

本機能は静的ページコンポーネントの変更のみのため、パフォーマンスへの影響は最小限です。空白ページは既存のウェルカムページよりも軽量になるため、パフォーマンスが向上する可能性があります。
