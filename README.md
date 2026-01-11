# バージョン管理サービスの生産分析ツール

Laravel 12 + React 19 + TypeScript 5.7 + Inertia.js 2.x ベースの Git バージョン管理サービス（GitLab）の生産分析ツール。モダンなフロントエンドとバックエンドの統合により、リポジトリ情報の管理と分析を提供します。

## 概要

本プロジェクトは、Git バージョン管理サービスの生産分析を行うためのツールです。クリーンアーキテクチャの原則に従って実装された Laravel アプリケーションで、GitLab API からプロジェクト一覧を取得し、データベースに永続化する機能を提供します。また、コミットの増分収集機能により、効率的にコミット情報を収集・管理できます。さらに、ユーザーごとの月次集計機能により、コミット作成者の生産性を月単位で分析できます。

### 主な特徴

- **クリーンアーキテクチャ**: ドメイン層、アプリケーション層、インフラストラクチャ層、プレゼンテーション層を明確に分離
- **型安全性**: TypeScript strict mode による型安全な開発環境
- **SPA 体験**: Inertia.js によるページ遷移なしのスムーズな UX
- **モダンな開発ツール**: Vite、ESLint、Prettier、Storybook による開発体験の最適化
- **コミット分析**: コミットの増分収集とユーザーごとの月次集計による生産性分析

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
- Recharts (グラフ表示)
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

## 機能

### コミットの月次集計

コミットデータから、ユーザー（コミット作成者）ごとの月次集計（追加行数と削除行数）を自動生成し、永続化する機能を提供します。

#### 主な機能

- **自動集計**: コミットの収集や再収集が完了すると、自動的に月次集計を実行
- **効率的な集計**: 最終集計月から先月までのデータのみを集計（今月は除外）
- **重複防止**: 同一のユーザー・プロジェクト・ブランチ・年月の集計データは更新され、重複は作成されない
- **可視化**: 棒グラフと表で集計データを視覚的に表示
  - 積み上げ棒グラフ: 追加行と削除行を積み上げて表示
  - 集合縦棒グラフ: 複数ユーザーのデータを比較表示
  - 表形式: ユーザーごと、月ごとの合計行数を表示

#### アクセス方法

集計画面にアクセスするには、以下の URL にアクセスしてください：

```
/commits/aggregation
```

#### 画面の使い方

1. **プロジェクト・ブランチの選択**: セレクトボックスからプロジェクトIDとブランチ名の組み合わせを選択
2. **年の選択**: セレクトボックスから集計対象の年を選択
3. **データの確認**: グラフと表で集計データを確認
   - グラフ: 横軸に月、縦軸に行数を表示。追加行は青色、削除行は赤色
   - 表: 縦軸にユーザー、横軸に月を表示。セルには合計行数（追加行数と削除行数の合計）を表示

### ユーザー生産性

複数リポジトリにまたがるユーザーごとの月次生産性を可視化する機能です。既存の「集計」機能と同様に、グラフと表でデータを表示しますが、集計単位がプロジェクト・ブランチ単位からユーザー単位に変更されます。

#### 主な機能

- **横断的な分析**: 複数リポジトリにまたがる同一ユーザーのコミットデータを統合表示
- **柔軟なフィルタリング**: 年フィルターとユーザー複数選択フィルターによる柔軟なデータ分析
- **可視化**: 棒グラフと表で集計データを視覚的に表示
  - 積み上げ棒グラフ: 追加行と削除行を積み上げて表示
  - 集合縦棒グラフ: 複数ユーザーのデータを比較表示
  - 表形式: ユーザーごと、月ごとの合計行数を表示

#### アクセス方法

ユーザー生産性画面にアクセスするには、以下の URL にアクセスしてください：

```
/commits/user-productivity
```

#### 画面の使い方

1. **年の選択**: セレクトボックスから集計対象の年を選択
2. **ユーザーの選択**: チェックボックスで表示したいユーザーを複数選択（全選択も可能）
3. **データの確認**: グラフと表で集計データを確認
   - グラフ: 横軸に月、縦軸に行数を表示。追加行は青色、削除行は赤色
   - 表: 縦軸にユーザー、横軸に月を表示。セルには合計行数（追加行数と削除行数の合計）を表示

**注意**: 年とユーザーの両方が選択されている場合のみ、集計データが表示されます。メモリーオーバーフローを防ぐため、フィルターが指定されていない場合は空のデータを返します。

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

### Push前チェック

GitHub Actions と同じチェックを push 前に実行できます。すべてのチェックを一括実行：

```bash
npm run pre-push
```

このコマンドは以下を実行します：
1. PHP コードスタイルチェック（Pint）
2. フロントエンドフォーマットチェック（Prettier）
3. フロントエンドリントチェック（ESLint）
4. TypeScript 型チェック
5. アセットビルドチェック
6. PHP ユニットテスト

#### 個別のチェック

各チェックを個別に実行することもできます：

```bash
# PHP コードスタイルチェック
npm run check:php

# フロントエンドフォーマットチェック
npm run check:format

# フロントエンドリントチェック
npm run check:lint

# TypeScript 型チェック
npm run check:types

# アセットビルドチェック
npm run check:build

# PHP ユニットテスト
npm run check:php-tests
```

#### Git Pre-Push フック

Git の pre-push フックが設定されており、`git push` を実行すると自動的にすべてのチェックが実行されます。チェックに失敗した場合、push は中断されます。

フックをスキップする場合（非推奨）：

```bash
git push --no-verify
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

## ライセンス

MIT
