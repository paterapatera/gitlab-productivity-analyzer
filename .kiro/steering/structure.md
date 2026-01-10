# Project Structure

## Organization Philosophy

**レイヤードアーキテクチャ**: バックエンド（Laravel）とフロントエンド（React）を明確に分離。Inertia.js が両者を統合。

**機能ベースのページ構造**: フロントエンドは機能ごとにページを分割（`pages/auth/`, `pages/settings/`）。

## Directory Patterns

### Backend (Laravel)
**Location**: `/app/`  
**Purpose**: アプリケーションロジック、コントローラー、モデル、ミドルウェア  
**Example**: `app/Http/Controllers/Controller.php`

**Location**: `/routes/`  
**Purpose**: ルート定義。`web.php` はメイン、`settings.php` は設定関連  
**Example**: `routes/web.php`, `routes/settings.php`

**Location**: `/database/migrations/`  
**Purpose**: データベーススキーマ定義  
**Example**: `database/migrations/0001_01_01_000000_create_users_table.php`

### Frontend (React/TypeScript)
**Location**: `/resources/js/pages/`  
**Purpose**: Inertia.js ページコンポーネント。機能ごとにサブディレクトリ  
**Example**: `pages/welcome.tsx`, `pages/settings/appearance.tsx`

**Location**: `/resources/js/components/`  
**Purpose**: 再利用可能な React コンポーネント  
**Example**: `components/app-header.tsx`, `components/ui/button.tsx`

**Location**: `/resources/js/components/ui/`  
**Purpose**: デザインシステムのプリミティブコンポーネント（Radix UI ベース）  
**Example**: `components/ui/button.tsx`, `components/ui/dialog.tsx`

**Location**: `/resources/js/layouts/`  
**Purpose**: ページレイアウトコンポーネント  
**Example**: `layouts/app-layout.tsx`, `layouts/auth-layout.tsx`

**Location**: `/resources/js/hooks/`  
**Purpose**: カスタム React Hooks  
**Example**: `hooks/use-appearance.tsx`

**Location**: `/resources/js/lib/`  
**Purpose**: ユーティリティ関数  
**Example**: `lib/utils.ts` (cn 関数、URL 変換など)

**Location**: `/resources/js/types/`  
**Purpose**: TypeScript 型定義  
**Example**: `types/index.d.ts`

**Location**: `/resources/js/test/`  
**Purpose**: フロントエンドテストファイル（Vitest + React Testing Library）  
**Example**: `test/example.test.tsx`, `test/setup.ts`

## Naming Conventions

- **Files**: PascalCase for components (`Button.tsx`), kebab-case for pages (`login.tsx`)
- **Components**: PascalCase (`AppHeader`, `UserMenuContent`)
- **Functions**: camelCase (`useAppearance`, `toUrl`)
- **PHP Classes**: PascalCase, PSR-4 autoloading (`ProfileController`)

## Import Organization

```typescript
// 絶対パス（@/ エイリアス）を優先
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';

// 相対パスは同一ディレクトリ内のファイルのみ
import { LocalComponent } from './local-component';
```

**Path Aliases**:
- `@/`: `resources/js/` にマッピング（`tsconfig.json` で設定）

## Code Organization Principles

- **コンポーネント分離**: UI プリミティブ（`components/ui/`）とビジネスロジックコンポーネント（`components/`）を分離
- **ページ = Inertia コンポーネント**: 各ページは Inertia.js のページコンポーネントとして実装
- **型安全性**: すべての props とデータ構造に型を定義
- **レイアウトの再利用**: 共通レイアウト（`AppLayout`, `AuthLayout`）を活用
- **コントローラー分離**: 機能ごとにコントローラーを分割（必要に応じて）
- **テスト分離**: フロントエンドテストは `resources/js/test/` に配置

---
_パターンを記述。ファイルツリーを列挙するものではない。既存のパターンに従う新しいファイルは更新不要_
