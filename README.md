# Gitバージョン管理サービスの生産分析ツール

Laravel 12 + React 19 + TypeScript 5.7 + Inertia.js 2.x ベースの Git バージョン管理サービス（GitLab）の生産分析ツール。モダンなフロントエンドとバックエンドの統合により、リポジトリ情報の管理と分析を提供します。

## 概要

本プロジェクトは、Git バージョン管理サービスの生産分析を行うためのツールです。クリーンアーキテクチャの原則に従って実装された Laravel アプリケーションで、GitLab API からプロジェクト一覧を取得し、データベースに永続化する機能を提供します。

### 主な特徴

- **クリーンアーキテクチャ**: ドメイン層、アプリケーション層、インフラストラクチャ層、プレゼンテーション層を明確に分離
- **型安全性**: TypeScript strict mode による型安全な開発環境
- **SPA 体験**: Inertia.js によるページ遷移なしのスムーズな UX
- **モダンな開発ツール**: Vite、ESLint、Prettier、Storybook による開発体験の最適化

## 技術スタック

### Backend
- PHP 8.2+
- Laravel 12
- Pest (テストフレームワーク)

### Frontend
- React 19
- TypeScript 5.7
- Inertia.js 2.x
- Tailwind CSS 4
- Radix UI
- Vitest (テストフレームワーク)
- Storybook (コンポーネント開発)

### Build Tool
- Vite 7

## セットアップ手順

### 必要な環境

- PHP 8.2+
- Composer
- Node.js (npm)
- PostgreSQL (データベース)

### インストール

1. リポジトリをクローン
```bash
git clone <repository-url>
cd <project-directory>
```

2. 依存関係のインストール
```bash
composer install
npm install
```

3. 環境変数の設定
```bash
cp .env.example .env
php artisan key:generate
```

4. データベースの設定
`.env` ファイルにデータベース接続情報を設定してください：
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. マイグレーションの実行
```bash
php artisan migrate
```

6. アセットのビルド
```bash
npm run build
```

## GitLab API 設定

GitLab API からプロジェクト一覧を取得する機能を使用するには、GitLab API の認証情報を設定する必要があります。

### 環境変数の設定

`.env` ファイルに以下の環境変数を追加してください：

```env
GITLAB_BASE_URL=https://gitlab.com
GITLAB_TOKEN=your_gitlab_private_token
```

### 設定の説明

- **GITLAB_BASE_URL**: GitLab インスタンスのベース URL
  - GitLab.com を使用する場合: `https://gitlab.com`
  - セルフホスト型 GitLab を使用する場合: `https://your-gitlab-instance.com`
- **GITLAB_TOKEN**: GitLab の Private Access Token
  - GitLab の設定画面（Settings → Access Tokens）から生成できます
  - 必要なスコープ: `api` または `read_api`

### 設定ファイル

GitLab API の設定は `config/services.php` で管理されています：

```php
'gitlab' => [
    'base_url' => env('GITLAB_BASE_URL'),
    'token' => env('GITLAB_TOKEN'),
],
```

## 開発コマンド

### 開発サーバーの起動

Laravel サーバー、Vite 開発サーバー、キュー、ログを同時に起動：

```bash
composer dev
```

このコマンドは以下を起動します：
- Laravel サーバー（`php artisan serve`）
- キューリスナー（`php artisan queue:listen`）
- ログビューアー（`php artisan pail`）
- Vite 開発サーバー（`npm run dev`）

### SSR モードで起動

サーバーサイドレンダリング（SSR）モードで起動：

```bash
composer dev:ssr
```

### ビルド

本番環境用のアセットをビルド：

```bash
npm run build
```

SSR 用のビルド：

```bash
npm run build:ssr
```

### テスト

#### バックエンドテスト（Pest）

```bash
composer test
```

または：

```bash
php artisan test
```

#### フロントエンドテスト（Vitest）

```bash
npm run test
```

UI モードでテストを実行：

```bash
npm run test:ui
```

### コードフォーマット

#### Prettier（フロントエンド）

コードをフォーマット：

```bash
npm run format
```

フォーマットのチェックのみ：

```bash
npm run format:check
```

#### ESLint

```bash
npm run lint
```

### Storybook

Storybook を起動：

```bash
npm run storybook
```

Storybook をビルド：

```bash
npm run build-storybook
```

### 型チェック

TypeScript の型チェック：

```bash
npm run types
```

## プロジェクト構造

### バックエンド（クリーンアーキテクチャ）

- `/app/Domain/`: ドメイン層（エンティティ、値オブジェクト）
- `/app/Application/`: アプリケーション層（ユースケース、サービス）
  - `/app/Application/Contract/`: サービスインターフェース
  - `/app/Application/Service/`: サービス実装
  - `/app/Application/Port/`: 外部システムとのインターフェース
- `/app/Infrastructure/`: インフラストラクチャ層（リポジトリ実装、外部サービス統合）
- `/app/Presentation/`: プレゼンテーション層（コントローラー、HTTP リクエスト/レスポンス）

### フロントエンド

- `/resources/js/pages/`: Inertia.js ページコンポーネント
- `/resources/js/components/ui/`: UI プリミティブコンポーネント
- `/resources/js/types/`: TypeScript 型定義
- `/resources/js/test/`: フロントエンドテスト
- `/stories/`: Storybook ストーリーファイル

## 主要な機能

### プロジェクト一覧機能

GitLab API からプロジェクト一覧を取得し、データベースに永続化する機能を提供します。

- **エンドポイント**:
  - `GET /projects`: プロジェクト一覧を表示
  - `POST /projects/sync`: GitLab API からプロジェクト情報を同期

詳細は `.kiro/specs/gitlab-repository-list/` を参照してください。

## ライセンス

MIT
