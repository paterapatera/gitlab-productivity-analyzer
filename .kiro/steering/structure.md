# Project Structure

## Organization Philosophy

**レイヤードアーキテクチャ**: バックエンド（Laravel）とフロントエンド（React）を明確に分離。Inertia.js が両者を統合。

**バックエンド: クリーンアーキテクチャ**: バックエンドはクリーンアーキテクチャの原則に従って実装。ドメイン層（Entities, Value Objects）、アプリケーション層（Use Cases, Services）、インフラストラクチャ層（Repositories, データアクセス実装）、プレゼンテーション層（Controllers, HTTP処理）を分離し、依存関係の方向を制御。これにより、ビジネスロジックの独立性とテスタビリティを確保。

**機能ベースのページ構造**: フロントエンドは機能ごとにページを分割（`pages/auth/`, `pages/settings/`）。

## Directory Patterns

### Backend (Laravel)
クリーンアーキテクチャに基づく構造：

**Location**: `/app/Domain/`  
**Purpose**: ドメイン層。エンティティ、値オブジェクト、ドメインサービス  
**Pattern**: フレームワーク非依存の純粋なビジネスロジック

**Location**: `/app/Application/`  
**Purpose**: アプリケーション層。ユースケース、アプリケーションサービス、DTO  
**Pattern**: ドメイン層に依存し、インフラストラクチャ層に依存しない

**Location**: `/app/Infrastructure/`  
**Purpose**: インフラストラクチャ層。リポジトリ実装、外部サービス統合、データアクセス  
**Pattern**: アプリケーション層のインターフェースを実装

**Location**: `/app/Presentation/`  
**Purpose**: プレゼンテーション層。コントローラー、HTTP リクエストの処理、Inertia.js レスポンス、リクエスト/レスポンスの変換  
**Pattern**: アプリケーション層のユースケースを呼び出し。ビジネスロジックは含まない

**Location**: `/app/Http/Middleware/`  
**Purpose**: HTTP ミドルウェア  
**Example**: `app/Http/Middleware/HandleInertiaRequests.php`

**Location**: `/routes/`  
**Purpose**: ルート定義。`web.php` はメインルート  
**Example**: `routes/web.php`

**Location**: `/database/migrations/`  
**Purpose**: データベーススキーマ定義  
**Example**: `database/migrations/0001_01_01_000000_create_users_table.php`

### Frontend (React/TypeScript)
**Location**: `/resources/js/pages/`  
**Purpose**: Inertia.js ページコンポーネント。機能ごとにサブディレクトリで整理  
**Example**: `pages/example.tsx`, `pages/settings/appearance.tsx` (将来の構造)

**Location**: `/resources/js/lib/`  
**Purpose**: ユーティリティ関数  
**Example**: `lib/utils.ts` (cn 関数など)

**Location**: `/resources/js/types/`  
**Purpose**: TypeScript 型定義  
**Example**: `types/index.d.ts`, `types/vite-env.d.ts`

**Location**: `/resources/js/test/`  
**Purpose**: フロントエンドテストファイル（Vitest + React Testing Library）  
**Example**: `test/setup.ts`, `test/vitest.d.ts`

### 推奨パターン（将来の拡張）
以下のディレクトリ構造は、プロジェクトの成長に合わせて推奨されるパターンです：

**Location**: `/resources/js/components/`  
**Purpose**: 再利用可能な React コンポーネント  
**Pattern**: ビジネスロジックを含むコンポーネント

**Location**: `/resources/js/components/ui/`  
**Purpose**: デザインシステムのプリミティブコンポーネント（Radix UI ベース）  
**Pattern**: スタイルのみの再利用可能な UI プリミティブ

**Location**: `/resources/js/layouts/`  
**Purpose**: ページレイアウトコンポーネント  
**Pattern**: 共通レイアウト（AppLayout, AuthLayout など）

**Location**: `/resources/js/hooks/`  
**Purpose**: カスタム React Hooks  
**Pattern**: 再利用可能なロジックの抽出

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

### Backend
- **クリーンアーキテクチャの原則**: 依存関係の方向は外側から内側へ（Presentation → Infrastructure → Application → Domain）。ドメイン層はフレームワーク非依存
- **層の分離**: 
  - **プレゼンテーション層**（`/app/Presentation/`）: コントローラー、HTTPリクエスト/レスポンスの処理、アプリケーション層のユースケース呼び出し
  - **アプリケーション層**（`/app/Application/`）: ユースケース、ビジネスロジックのオーケストレーション、ドメイン層の利用
  - **ドメイン層**（`/app/Domain/`）: エンティティ、値オブジェクト、純粋なビジネスロジック、フレームワーク非依存
  - **インフラストラクチャ層**（`/app/Infrastructure/`）: リポジトリ実装、データアクセス実装、外部サービス統合
- **依存関係の逆転**: アプリケーション層が定義するインターフェース（例: リポジトリインターフェース）を、インフラストラクチャ層が実装
- **コントローラーの薄さ**: コントローラー（`/app/Presentation/`）は HTTP リクエストの処理とユースケースの呼び出しのみ。ビジネスロジックは含まない
- **リポジトリパターン**: データアクセスはリポジトリインターフェース（`/app/Application/`）を通じて行い、実装はインフラストラクチャ層（`/app/Infrastructure/`）に配置

### Frontend
- **ページ = Inertia コンポーネント**: 各ページは Inertia.js のページコンポーネントとして実装（`resources/js/pages/`）
- **型安全性**: すべての props とデータ構造に型を定義（`resources/js/types/`）
- **ユーティリティの集約**: 共通関数は `lib/` に配置（例: `lib/utils.ts`）
- **テスト分離**: フロントエンドテストは `resources/js/test/` に配置
- **将来の拡張**: コンポーネント、レイアウト、フックは必要に応じて追加（推奨パターン参照）

---
_パターンを記述。ファイルツリーを列挙するものではない。既存のパターンに従う新しいファイルは更新不要_
