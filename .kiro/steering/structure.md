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

**Location**: `/app/Application/Service/`  
**Purpose**: アプリケーションサービスの実体  
**Pattern**: サービスの実装クラス。トランザクション管理が必要なサービスは `BaseService` を継承し、`transaction()` メソッドと `handleErrors()` メソッドを利用。トランザクションが不要なサービス（例: `GetProjects`）は直接インターフェースを実装

**Location**: `/app/Application/Contract/`  
**Purpose**: アプリケーションサービスのインターフェース  
**Pattern**: サービスインターフェースの定義。アプリケーション層のユースケースを表現

**Location**: `/app/Application/Port/`  
**Purpose**: 外部システムとのインターフェース（Ports and Adapters パターン）  
**Pattern**: データアクセス（リポジトリ）や外部API（GitLab API等）とのインターフェース。インフラストラクチャ層が実装

**Location**: `/app/Infrastructure/`  
**Purpose**: インフラストラクチャ層。リポジトリ実装、外部サービス統合、データアクセス  
**Pattern**: アプリケーション層のインターフェース（Port、Contract）を実装

**Location**: `/app/Infrastructure/Repositories/`  
**Purpose**: リポジトリ実装とEloquentモデル  
**Pattern**: `EloquentProjectRepository` が `ProjectRepository` Port を実装。Eloquentモデルは `Repositories/Eloquent/` に配置

**Location**: `/app/Presentation/`  
**Purpose**: プレゼンテーション層。コントローラー、HTTP リクエストの処理、Inertia.js レスポンス、リクエスト/レスポンスの変換  
**Pattern**: アプリケーション層のユースケースを呼び出し。ビジネスロジックは含まない

**Location**: `/app/Presentation/Request/`  
**Purpose**: HTTP リクエストのバリデーションと変換  
**Pattern**: `BaseRequest` を継承し、`rules()` メソッドでバリデーションルールを定義。機能ごとにサブディレクトリで整理（例: `Request/Commit/`, `Request/Project/`）

**Location**: `/app/Presentation/Response/`  
**Purpose**: アプリケーション層の結果を Inertia.js に渡すための配列に変換  
**Pattern**: `toArray()` メソッドで Inertia.js に渡す配列を返す。機能ごとにサブディレクトリで整理（例: `Response/Commit/`, `Response/Project/`）。共通の変換ロジックはトレイト（例: `ConvertsProjectsToArray`）で共有

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
**Example**: `pages/Project/Index.tsx`, `pages/Commit/Index.tsx`, `pages/example.tsx`

**Location**: `/resources/js/lib/`  
**Purpose**: ユーティリティ関数  
**Example**: `lib/utils.ts` (cn 関数など)

**Location**: `/resources/js/types/`  
**Purpose**: TypeScript 型定義  
**Example**: `types/index.d.ts`, `types/vite-env.d.ts`

**Location**: `/resources/js/test/`  
**Purpose**: フロントエンドテストファイル（Vitest + React Testing Library）  
**Example**: `test/setup.ts`, `test/vitest.d.ts`

**Location**: `/resources/js/components/ui/`  
**Purpose**: デザインシステムのプリミティブコンポーネント（Radix UI ベース）  
**Pattern**: スタイルのみの再利用可能な UI プリミティブ  
**Example**: `components/ui/button.tsx`, `components/ui/table.tsx`

**Location**: `/resources/js/components/common/`  
**Purpose**: 共通のビジネスロジックを含むコンポーネント  
**Pattern**: 複数のページで再利用されるコンポーネント（FlashMessage、LoadingButton、PageLayout など）  
**Example**: `components/common/FlashMessage.tsx`, `components/common/PageLayout.tsx`

**Location**: `/stories/`  
**Purpose**: Storybook ストーリーファイル。ページコンポーネントの開発とドキュメント化  
**Pattern**: ページ構造と対応（`stories/Project/Index.stories.tsx` は `pages/Project/Index.tsx` に対応）。モックは `stories/mocks/` に配置  
**Example**: `stories/Project/Index.stories.tsx`, `stories/mocks/inertia.tsx`

### 推奨パターン（将来の拡張）
以下のディレクトリ構造は、プロジェクトの成長に合わせて推奨されるパターンです：

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
- **依存関係の逆転**: アプリケーション層が定義するインターフェース（Port、Contract）を、インフラストラクチャ層やサービス実装が実装
- **Port と Contract の分離**: 
  - **Port** (`/app/Application/Port/`): 外部システム（データベース、外部API）とのインターフェース。インフラストラクチャ層が実装
  - **Contract** (`/app/Application/Contract/`): アプリケーションサービスのインターフェース。アプリケーション層のユースケースを定義。サービス実装（`/app/Application/Service/`）が実装
- **サービス層の分離**: サービスインターフェース（`/app/Application/Contract/`）とサービス実装（`/app/Application/Service/`）を分離し、依存関係の逆転を実現
- **BaseService パターン**: すべてのアプリケーションサービスは `BaseService` を継承し、トランザクション管理（`transaction()` メソッド）とエラーハンドリングの統一パターンを利用
- **コントローラーの薄さ**: コントローラー（`/app/Presentation/`）は HTTP リクエストの処理とユースケースの呼び出しのみ。ビジネスロジックは含まない
- **リポジトリパターン**: データアクセスはリポジトリインターフェース（`/app/Application/Port/ProjectRepository`）を通じて行い、実装はインフラストラクチャ層（`/app/Infrastructure/Repositories/`）に配置

### Frontend
- **ページ = Inertia コンポーネント**: 各ページは Inertia.js のページコンポーネントとして実装（`resources/js/pages/`）
- **型安全性**: すべての props とデータ構造に型を定義（`resources/js/types/`）
- **ユーティリティの集約**: 共通関数は `lib/` に配置（例: `lib/utils.ts`）
- **テスト分離**: フロントエンドテストは `resources/js/test/` に配置
- **UI コンポーネント**: デザインシステムのプリミティブは `components/ui/` に配置（Radix UI ベース）
- **Storybook**: ページコンポーネントの開発とドキュメント化は `stories/` に配置。ページ構造と対応するパターン
- **将来の拡張**: ビジネスロジックを含むコンポーネント、レイアウト、フックは必要に応じて追加（推奨パターン参照）

---
_パターンを記述。ファイルツリーを列挙するものではない。既存のパターンに従う新しいファイルは更新不要_
