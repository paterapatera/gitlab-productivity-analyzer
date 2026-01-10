# Implementation Gap Analysis

## 1. Current State Investigation

### 既存のドメイン関連アセット

**ディレクトリ構造**:
- `/app/` には基本的なLaravel構造のみ（`Http/Controllers/`, `Providers/`）
- クリーンアーキテクチャの層（`Domain/`, `Application/`, `Infrastructure/`, `Presentation/`）は未実装
- `/database/migrations/` には基本的なテーブル（users, cache, jobs）のみ
- `/resources/js/pages/` には基本的なInertia.jsページ構造のみ

**再利用可能なコンポーネント**:
- 外部API統合のパターンや実装例が存在しない
- HTTPクライアントのラッパーやエラーハンドリングの共通実装がない
- データモデルやリポジトリパターンの実装例がない

**統合ポイント**:
- Laravel標準のHTTPクライアント（Guzzle）が利用可能
- Inertia.jsによるフロントエンド統合パターンは確立済み
- データベース接続とマイグレーション機能は利用可能

### 既存の規約

**命名規則**:
- PHPクラス: PascalCase、PSR-4 autoloading
- ファイル: kebab-case for pages、PascalCase for components
- データベース: snake_case、複数形テーブル名

**アーキテクチャパターン**:
- クリーンアーキテクチャの原則が定義されているが、実装は未開始
- 依存関係の方向: Presentation → Infrastructure → Application → Domain
- リポジトリパターンが推奨されているが、実装例がない

**テスト配置**:
- Pestを使用したPHPテスト構造
- Vitest + React Testing Libraryによるフロントエンドテスト構造

## 2. Requirements Feasibility Analysis

### 技術的必要要素

**データモデル**:
- リポジトリエンティティ（ID、名前、URL、説明、その他のメタデータ）
- データベーステーブル定義（`repositories`テーブル）
- マイグレーションファイル

**API/サービス**:
- GitLab APIクライアント（HTTPリクエスト、認証、ページネーション処理）
- リポジトリ取得ユースケース
- リポジトリ永続化ユースケース
- リポジトリ同期ユースケース

**UI/コンポーネント**:
- リポジトリ一覧ページ（Inertia.jsコンポーネント）
- 同期ボタン/UI（手動同期トリガー）
- ローディング状態表示
- エラー状態表示
- 空状態表示

**ビジネスルール/検証**:
- リポジトリ情報の必須フィールド検証
- 既存リポジトリの更新ロジック
- 削除されたリポジトリの検出と処理

**非機能要件**:
- セキュリティ: GitLab APIトークンの安全な管理
- パフォーマンス: ページネーション対応、大量データ処理
- 信頼性: エラーハンドリング、リトライ戦略、タイムアウト処理

### ギャップと制約

**Missing（不足している機能）**:
- ✅ クリーンアーキテクチャの層構造（Domain, Application, Infrastructure, Presentation）
- ✅ GitLab API統合の実装
- ✅ リポジトリエンティティとドメインモデル
- ✅ リポジトリリポジトリインターフェースと実装
- ✅ ユースケースクラス（取得、永続化、同期）
- ✅ データベースマイグレーション（repositoriesテーブル）
- ✅ フロントエンドページコンポーネント
- ✅ エラーハンドリングの共通パターン

**Unknown（調査が必要）**:
- ⚠️ GitLab APIの詳細なレスポンス構造（設計フェーズで調査）
- ⚠️ リポジトリ情報の完全なフィールドセット（設計フェーズで調査）
- ⚠️ ページネーションの実装詳細（設計フェーズで調査）

**Constraint（既存アーキテクチャからの制約）**:
- クリーンアーキテクチャの原則に従う必要がある
- Inertia.jsを使用したフロントエンド統合
- Laravel標準のHTTPクライアントを使用

### 複雑度シグナル

- **外部統合**: GitLab APIとの統合（中程度の複雑度）
- **ワークフロー**: 取得 → 永続化 → 表示のフロー（低〜中程度）
- **データ管理**: CRUD操作と同期処理（中程度の複雑度）

## 3. Implementation Approach Options

### Option A: Extend Existing Components

**検討結果**: 不適切

**理由**:
- クリーンアーキテクチャの層構造が未実装のため、拡張する既存コンポーネントが存在しない
- 外部API統合のパターンが存在しないため、拡張の基盤がない

### Option B: Create New Components（推奨）

**検討結果**: 適切

**新規作成が必要なコンポーネント**:

**ドメイン層** (`/app/Domain/`):
- `Repository` エンティティ
- リポジトリ関連の値オブジェクト（必要に応じて）

**アプリケーション層** (`/app/Application/`):
- `GetRepositoriesFromGitLabUseCase` - GitLab APIからリポジトリ一覧を取得
- `PersistRepositoriesUseCase` - リポジトリ情報をデータベースに保存
- `SyncRepositoriesUseCase` - リポジトリ情報を同期
- `RepositoryRepositoryInterface` - リポジトリデータアクセスのインターフェース
- DTOクラス（必要に応じて）

**インフラストラクチャ層** (`/app/Infrastructure/`):
- `GitLabApiClient` - GitLab APIとの通信を担当
- `EloquentRepositoryRepository` - リポジトリリポジトリのEloquent実装

**プレゼンテーション層** (`/app/Presentation/`):
- `RepositoryController` - HTTPリクエストの処理、Inertia.jsレスポンス（同期エンドポイント含む）

**データベース**:
- `create_repositories_table` マイグレーション

**フロントエンド** (`/resources/js/pages/`):
- `repositories/index.tsx` - リポジトリ一覧ページ（同期ボタン含む）

**統合ポイント**:
- ルート定義（`routes/web.php`）に新しいエンドポイントを追加
- サービスプロバイダー（`AppServiceProvider`）でリポジトリインターフェースのバインディングを登録
- 設定ファイル（`config/services.php`）にGitLab API設定を追加

**責任境界**:
- **GitLab API統合**: インフラストラクチャ層が担当（`GitLabApiClient`）
- **ビジネスロジック**: アプリケーション層が担当（ユースケース）
- **データアクセス**: インフラストラクチャ層が担当（Eloquent実装）
- **HTTP処理**: プレゼンテーション層が担当（コントローラー）
- **UI表示**: フロントエンドが担当（Inertia.jsページ）

**Trade-offs**:
- ✅ クリーンアーキテクチャの原則に従った明確な分離
- ✅ 各層を独立してテスト可能
- ✅ 将来の拡張や変更に柔軟に対応可能
- ✅ プロジェクトのアーキテクチャパターンを確立
- ❌ 初期実装のファイル数が多い
- ❌ クリーンアーキテクチャの理解が必要

### Option C: Hybrid Approach

**検討結果**: 不適用

**理由**:
- 既存の拡張可能なコンポーネントが存在しないため、ハイブリッドアプローチの対象がない

## 4. Implementation Complexity & Risk

### Effort: **M (3-7日)**

**根拠**:
- クリーンアーキテクチャの層構造の新規作成（1-2日）
- GitLab API統合の実装（1-2日）
- データモデルとマイグレーション（0.5日）
- ユースケースの実装（1日）
- フロントエンドページの実装（同期UI含む、1日）
- テストの作成（1日）

### Risk: **Medium**

**根拠**:
- **新しいパターンの導入**: クリーンアーキテクチャの構造を新規作成する必要があるが、明確なガイダンス（steering）が存在
- **外部API統合**: GitLab APIの統合は標準的なHTTPクライアント使用で対応可能
- **明確なスコープ**: 要件が明確で、実装範囲が限定的
- **既知の技術**: Laravel、React、TypeScriptは既存の技術スタック

**リスク要因**:
- GitLab APIの詳細な仕様や制限事項の調査が必要
- ページネーション処理の実装詳細
- エラーハンドリングの一貫性確保

## 5. Recommendations for Design Phase

### 推奨アプローチ

**Option B（新規コンポーネント作成）を推奨**

**主要な決定事項**:
1. **クリーンアーキテクチャの層構造を新規作成**: プロジェクトのアーキテクチャパターンを確立する最初の機能として実装
2. **GitLab API統合**: インフラストラクチャ層に`GitLabApiClient`を配置し、認証とページネーション処理を実装
3. **データモデル設計**: リポジトリ情報に必要なフィールドを特定し、正規化されたテーブル構造を設計
4. **同期処理**: 手動同期方式を採用（ユーザーがボタンをクリックして同期を実行）。リアルタイム処理で実装
5. **エラーハンドリング**: 共通のエラーハンドリングパターンを確立（設計フェーズで詳細化）

### 設計フェーズで調査が必要な項目

1. **GitLab APIの詳細仕様**:
   - プロジェクト一覧エンドポイント（`/api/v4/projects`）の完全なレスポンス構造
   - ページネーションの実装方法（offset/limit vs cursor-based）
   - レート制限と推奨されるリクエスト間隔
   - 認証トークンの種類と権限要件

2. **リポジトリ情報のフィールド**:
   - GitLab APIから取得可能な全フィールド
   - 必須フィールドとオプショナルフィールド
   - データ型と制約

3. **同期処理の実装方法**:
   - 手動同期トリガー（ユーザーがボタンをクリックして同期を実行）
   - 削除されたリポジトリの検出方法
   - 部分的な更新失敗時の処理戦略
   - 同期中のUI状態管理（ローディング、進捗表示など）

4. **パフォーマンス考慮事項**:
   - 大量のリポジトリがある場合の処理方法
   - データベースインデックスの設計
   - フロントエンドでのページネーション実装

## 6. Requirement-to-Asset Map

| 要件 | 必要なアセット | 状態 | タグ |
|------|---------------|------|------|
| GitLab APIからのリポジトリ一覧取得 | `GitLabApiClient` (Infrastructure) | Missing | 新規作成 |
| GitLab APIからのリポジトリ一覧取得 | `GetRepositoriesFromGitLabUseCase` (Application) | Missing | 新規作成 |
| リポジトリ情報の永続化 | `Repository` Entity (Domain) | Missing | 新規作成 |
| リポジトリ情報の永続化 | `RepositoryRepositoryInterface` (Application) | Missing | 新規作成 |
| リポジトリ情報の永続化 | `EloquentRepositoryRepository` (Infrastructure) | Missing | 新規作成 |
| リポジトリ情報の永続化 | `PersistRepositoriesUseCase` (Application) | Missing | 新規作成 |
| リポジトリ情報の永続化 | `repositories` テーブル (Database) | Missing | 新規作成 |
| リポジトリ一覧の表示 | `RepositoryController` (Presentation) | Missing | 新規作成 |
| リポジトリ一覧の表示 | `repositories/index.tsx` (Frontend) | Missing | 新規作成 |
| リポジトリ情報の同期 | `SyncRepositoriesUseCase` (Application) | Missing | 新規作成 |
| リポジトリ情報の同期 | 同期エンドポイント（`RepositoryController::sync`） | Missing | 新規作成 |
| リポジトリ情報の同期 | 同期ボタンUI（`repositories/index.tsx`） | Missing | 新規作成 |
| GitLab API認証 | 設定ファイル（`config/services.php`） | Missing | 新規作成 |
| エラーハンドリング | 共通エラーハンドリングパターン | Missing | 新規作成 |
| ページネーション処理 | GitLab APIページネーション実装 | Unknown | 設計フェーズで調査 |

**凡例**:
- **Missing**: 完全に不足している機能/コンポーネント
- **Unknown**: 実装前に調査が必要な項目
