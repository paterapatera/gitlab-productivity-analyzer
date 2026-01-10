# Technology Stack

## Architecture

**SPA アーキテクチャ**: Laravel をバックエンド API として、Inertia.js で React フロントエンドと統合。ページ遷移は Inertia.js が管理し、従来の SPA のような体験を提供。

**SSR 対応**: Vite の SSR ビルド設定により、サーバーサイドレンダリングが可能。

## Core Technologies

- **Backend**: PHP 8.2+, Laravel 12
- **Frontend**: React 19, TypeScript 5.7
- **Bridge**: Inertia.js 2.x (Laravel + React)
- **Build Tool**: Vite 7
- **Styling**: Tailwind CSS 4, Radix UI
- **Runtime**: Node.js (開発環境)

## Key Libraries

### Backend
- **Laravel Wayfinder**: 型安全なルート生成

### Frontend
- **@inertiajs/react**: Inertia.js React アダプター
- **@radix-ui/***: アクセシブルな UI プリミティブ
- **class-variance-authority**: コンポーネントバリアント管理
- **tailwind-merge**: Tailwind クラスのマージ
- **lucide-react**: アイコンライブラリ

## Development Standards

### Type Safety
- TypeScript strict mode 有効
- `noImplicitAny` 有効
- 型定義は `resources/js/types/` に配置

### Code Quality
- **ESLint**: React Hooks、TypeScript ルール
- **Prettier**: コードフォーマット、import 整理
- **React Compiler**: Babel プラグインで最適化

### Testing
- **Pest**: PHP テストフレームワーク
- **Laravel Testing**: Feature/Unit テスト構造
- **Vitest**: フロントエンドテストフレームワーク（React Testing Library 統合）

## Development Environment

### Required Tools
- PHP 8.2+
- Composer
- Node.js (npm)
- Laravel Sail (Docker 環境、オプション)

### Common Commands
```bash
# 開発サーバー起動（Laravel + Vite + Queue + Logs）
composer dev

# SSR モードで起動
composer dev:ssr

# ビルド
npm run build

# テスト実行
composer test

# コードフォーマット
npm run format
npm run lint
```

## Key Technical Decisions

- **Inertia.js 採用**: 従来の API 開発を避け、Laravel のルーティングと React を直接統合
- **TypeScript 厳格モード**: 型安全性を最優先
- **Radix UI**: アクセシビリティとカスタマイズ性を両立
- **Tailwind CSS 4**: 最新のユーティリティファースト CSS フレームワーク
- **React 19**: 最新の React 機能を活用
- **Vite**: 高速な開発体験とビルド
- **Vitest**: フロントエンドテストに Vitest を採用（React Testing Library と統合）

---
_標準とパターンを記述。すべての依存関係を列挙するものではない_
