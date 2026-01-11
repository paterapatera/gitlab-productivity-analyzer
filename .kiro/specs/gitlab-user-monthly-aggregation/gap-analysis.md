# Implementation Gap Analysis

## Analysis Summary

- **スコープ**: 既存の「集計」機能と類似したUI/UXを提供しつつ、集計単位をプロジェクト・ブランチ単位からユーザー単位に変更。複数リポジトリにまたがるユーザーの生産性を可視化
- **主要なギャップ**: リポジトリに全ユーザー取得メソッドが存在しない、チェックボックスUIコンポーネントが未実装、ユーザー単位での集計ロジックが未実装
- **実装アプローチ**: 既存パターンを踏襲しつつ、新規コンポーネントを作成（Hybrid Approach推奨）
- **複雑度**: 中程度（M）- 既存パターンの再利用が可能だが、新規ロジックとUIコンポーネントが必要
- **リスク**: 低〜中程度 - 既存のアーキテクチャパターンに沿った実装が可能

## Current State Investigation

### Existing Assets

#### Backend Components
- **Domain Layer**: `CommitUserMonthlyAggregation` エンティティ（既存、変更不要）
- **Repository Interface**: `CommitUserMonthlyAggregationRepository` Port（既存、拡張必要）
- **Repository Implementation**: `EloquentCommitUserMonthlyAggregationRepository`（既存、新規メソッド追加必要）
- **Controller**: `CommitController`（既存、新規メソッド追加）
- **Request**: `AggregationShowRequest`（既存、参考になるが新規作成）
- **Response**: `AggregationShowResponse`（既存、参考になるが新規作成）

#### Frontend Components
- **Page Component**: `Commit/Aggregation.tsx`（既存、参考になるが新規作成）
- **UI Components**: `Select`, `Table`, `Button`（既存、再利用可能）
- **Chart Library**: Recharts（既存、再利用可能）
- **Checkbox Component**: 未実装（新規作成必要）

#### Data Access Patterns
- 既存の`findByProjectAndBranch`メソッドはプロジェクト・ブランチ単位でデータを取得
- 新機能ではプロジェクト・ブランチを指定せず、全ユーザーのデータを取得する必要がある

### Architecture Patterns

#### Backend Patterns
- **クリーンアーキテクチャ**: プレゼンテーション層 → アプリケーション層 → ドメイン層 → インフラストラクチャ層
- **Port and Adapters**: リポジトリインターフェース（Port）と実装（Adapter）を分離
- **BaseService**: トランザクション管理が必要なサービスは`BaseService`を継承（本機能では不要の可能性）
- **Request/Response DTOs**: プレゼンテーション層でリクエスト/レスポンスを変換

#### Frontend Patterns
- **Inertia.js**: ページコンポーネントとして実装
- **TypeScript**: 厳格モード、型定義は`types/`に配置
- **Component Organization**: UIプリミティブは`components/ui/`、共通コンポーネントは`components/common/`
- **Storybook**: ページコンポーネントの開発とドキュメント化

## Requirements Feasibility Analysis

### Technical Needs

#### Data Access
- **Missing**: プロジェクト・ブランチを指定せず、全ユーザーの集計データを取得するメソッド
- **Missing**: 年とユーザー（author_email）の配列でフィルタリングするメソッド
- **Missing**: 利用可能なユーザー一覧を取得するメソッド
- **Existing**: `CommitUserMonthlyAggregationRepository`インターフェースと実装は存在

#### Business Logic
- **Missing**: 複数リポジトリにまたがる同一ユーザーのデータを統合するロジック（月ごとに`total_additions`、`total_deletions`、`commit_count`を合計）
- **Existing**: 既存の`AggregationShowResponse`に類似のロジックがあるが、ユーザーキーの生成方法が異なる（既存: `project_id-branch_name-author_email`、新機能: `author_email`のみ）

#### Presentation Layer
- **Missing**: ユーザー生産性画面用のコントローラーメソッド
- **Missing**: ユーザー生産性画面用のリクエストクラス（年とユーザー配列のフィルタリング）
- **Missing**: ユーザー生産性画面用のレスポンスクラス（ユーザー単位での集計データ変換）

#### Frontend
- **Missing**: チェックボックスUIコンポーネント（Radix UIベース）
- **Missing**: ユーザー生産性画面のページコンポーネント
- **Missing**: ユーザー生産性画面用の型定義
- **Missing**: ユーザー生産性画面用のStorybookストーリー
- **Existing**: 既存の`Commit/Aggregation.tsx`を参考にできる

### Constraints

#### Data Model
- `CommitUserMonthlyAggregation`テーブルは既存のスキーマを使用（新規テーブル作成なし）
- データは`project_id`、`branch_name`、`author_email`、`year`、`month`で識別される
- 新機能では`project_id`と`branch_name`を無視して、`author_email`、`year`、`month`でグループ化する必要がある

#### Repository Interface
- 既存の`CommitUserMonthlyAggregationRepository`インターフェースに新規メソッドを追加する必要がある
- 既存の`findByProjectAndBranch`メソッドはプロジェクト・ブランチ単位の取得に特化しているため、新規メソッドが必要

#### UI/UX Consistency
- 既存の「集計」機能と同様のUI/UXを提供する必要がある
- グラフと表の表示パターンは既存機能と同様

## Implementation Approach Options

### Option A: Extend Existing Components

#### Which Files to Extend
- `CommitUserMonthlyAggregationRepository`インターフェース: 新規メソッド追加
  - `findAllUsers()`: 利用可能なユーザー一覧を取得
  - `findByUsersAndYear(array $authorEmails, ?int $year)`: ユーザー配列と年でフィルタリング
- `EloquentCommitUserMonthlyAggregationRepository`: 新規メソッド実装
- `CommitController`: 新規メソッド`userProductivityShow()`追加
- `routes/web.php`: 新規ルート追加

#### Compatibility Assessment
- ✅ 既存のインターフェースに新規メソッドを追加しても既存機能に影響なし
- ✅ 既存のコントローラーに新規メソッドを追加しても既存機能に影響なし
- ⚠️ リポジトリインターフェースの拡張は既存の実装に影響する可能性があるが、新規メソッドの追加のみなので問題なし

#### Complexity and Maintainability
- ⚠️ `CommitController`に複数の集計関連メソッドが集約される（`aggregationShow`と`userProductivityShow`）
- ⚠️ リポジトリインターフェースが肥大化する可能性

**Trade-offs**:
- ✅ 既存パターンを最大限活用
- ✅ 新規ファイル作成が最小限
- ❌ コントローラーとリポジトリが複雑化する可能性
- ❌ 単一責任の原則に反する可能性

### Option B: Create New Components

#### Rationale for New Creation
- ユーザー生産性機能は既存の集計機能とは異なる責任を持つ（プロジェクト・ブランチ単位 vs ユーザー単位）
- 既存の`CommitController`は既に複数の責任を持っている（collect, recollect, aggregation）
- 新しいリポジトリメソッドは既存の`findByProjectAndBranch`とは異なるクエリパターンを使用

#### Integration Points
- **New Controller**: `UserProductivityController`（`CommitController`とは別）
- **New Request**: `UserProductivityShowRequest`
- **New Response**: `UserProductivityShowResponse`
- **New Repository Methods**: `CommitUserMonthlyAggregationRepository`インターフェースに新規メソッド追加（実装は既存リポジトリに追加）
- **New Service** (Optional): `GetUserProductivity`サービス（集計ロジックを分離する場合）

#### Responsibility Boundaries
- `UserProductivityController`: ユーザー生産性画面の表示のみ
- `UserProductivityShowRequest`: 年とユーザー配列のバリデーション
- `UserProductivityShowResponse`: ユーザー単位での集計データ変換
- Repository新規メソッド: プロジェクト・ブランチを指定しないデータ取得

**Trade-offs**:
- ✅ 責任の分離が明確
- ✅ 既存の`CommitController`への影響なし
- ✅ テストが容易
- ❌ 新規ファイルが増える
- ❌ 既存パターンとの一貫性を保つ必要がある

### Option C: Hybrid Approach (推奨)

#### Combination Strategy
- **Extend**: リポジトリインターフェースと実装に新規メソッドを追加（データアクセス層）
- **Create New**: コントローラー、リクエスト、レスポンス、ページコンポーネントを新規作成（プレゼンテーション層）
- **Create New**: チェックボックスUIコンポーネントを新規作成（フロントエンド）

#### Phased Implementation
1. **Phase 1**: リポジトリ層の拡張（データアクセス）
   - `CommitUserMonthlyAggregationRepository`に新規メソッド追加
   - `EloquentCommitUserMonthlyAggregationRepository`に実装追加
2. **Phase 2**: バックエンドのプレゼンテーション層
   - `UserProductivityController`作成
   - `UserProductivityShowRequest`作成
   - `UserProductivityShowResponse`作成（集計ロジック含む）
3. **Phase 3**: フロントエンド
   - チェックボックスUIコンポーネント作成
   - ユーザー生産性ページコンポーネント作成
   - 型定義とStorybookストーリー作成

#### Risk Mitigation
- 既存の`CommitController`への影響を最小化
- リポジトリインターフェースの拡張は新規メソッド追加のみで既存機能に影響なし
- 段階的な実装により、各フェーズでテスト可能

**Trade-offs**:
- ✅ 責任の分離と既存パターンの活用のバランス
- ✅ 既存機能への影響を最小化
- ✅ 段階的な実装が可能
- ⚠️ 実装計画がやや複雑

## Requirement-to-Asset Map

### Requirement 1: ユーザー月次集計データの取得
- **Missing**: `CommitUserMonthlyAggregationRepository::findByUsersAndYear(array $authorEmails, ?int $year)`メソッド
- **Missing**: プロジェクト・ブランチを指定しないデータ取得ロジック

### Requirement 2: ユーザー単位での集計計算
- **Missing**: 複数リポジトリにまたがる同一ユーザーのデータを統合するロジック
- **Existing**: 既存の`AggregationShowResponse::buildChartData()`と`buildTableData()`を参考にできるが、ユーザーキーの生成方法が異なる

### Requirement 3: グラフ表示機能
- **Existing**: Rechartsライブラリと既存のグラフ実装パターン
- **Missing**: ユーザー単位でのグラフデータ構築ロジック

### Requirement 4: 表表示機能
- **Existing**: Table UIコンポーネントと既存の表実装パターン
- **Missing**: ユーザー単位での表データ構築ロジック

### Requirement 5: フィルタリング機能
- **Missing**: 年フィルターの実装（既存のAggregationと同様）
- **Missing**: ユーザー複数選択フィルターの実装（チェックボックス）

### Requirement 6: 年一覧の取得
- **Missing**: `CommitUserMonthlyAggregationRepository::findAvailableYears()`メソッド
- **Existing**: 既存の`aggregationShow`メソッドで同様のロジックがあるが、プロジェクト・ブランチ単位

### Requirement 7: ユーザー一覧の取得と選択機能
- **Missing**: `CommitUserMonthlyAggregationRepository::findAllUsers()`メソッド
- **Missing**: チェックボックスUIコンポーネント
- **Missing**: チェックボックスの状態管理ロジック

### Requirement 8: データ永続化の利用
- **Existing**: `CommitUserMonthlyAggregation`テーブルとリポジトリは既存
- **Constraint**: 新規テーブル作成なし

## Implementation Complexity & Risk

### Effort: M (3-7 days)
- **理由**: 既存パターンの再利用が可能だが、新規ロジック（ユーザー単位での集計）とUIコンポーネント（チェックボックス）の実装が必要
- **内訳**:
  - リポジトリ層の拡張: 1日
  - バックエンドのプレゼンテーション層: 1-2日
  - フロントエンド（チェックボックスコンポーネント含む）: 2-3日
  - テスト: 1日

### Risk: Low-Medium
- **Low Risk Factors**:
  - 既存のアーキテクチャパターンに沿った実装が可能
  - 既存のUIコンポーネント（Select, Table, Chart）を再利用可能
  - 既存のデータモデルを使用（新規テーブル作成なし）
- **Medium Risk Factors**:
  - チェックボックスUIコンポーネントの新規実装（Radix UIのパターンに従う必要がある）
  - 複数リポジトリにまたがる集計ロジックの正確性（既存のAggregationロジックを参考にできるが、ユーザーキーの生成方法が異なる）

## Recommendations for Design Phase

### Preferred Approach: Option C (Hybrid Approach)
- リポジトリ層は拡張、プレゼンテーション層は新規作成
- 既存機能への影響を最小化しつつ、責任の分離を実現

### Key Decisions
1. **リポジトリメソッドの設計**:
   - `findAllUsers(): Collection<UserInfo>`: 利用可能なユーザー一覧を返却（author_email, author_nameのペア）
   - `findByUsersAndYear(array $authorEmails, ?int $year): Collection<CommitUserMonthlyAggregation>`: ユーザー配列と年でフィルタリング（プロジェクト・ブランチは指定しない）
   - `findAvailableYears(): Collection<int>`: 利用可能な年一覧を返却

2. **集計ロジックの配置**:
   - `UserProductivityShowResponse`に集計ロジックを配置（既存の`AggregationShowResponse`と同様）
   - ユーザーキーは`author_email`のみを使用（既存は`project_id-branch_name-author_email`）

3. **UIコンポーネントの実装**:
   - Radix UIの`@radix-ui/react-checkbox`を使用してチェックボックスコンポーネントを実装
   - 既存のUIコンポーネント（`button.tsx`, `select.tsx`など）のパターンに従う

4. **ルーティング**:
   - `/commits/user-productivity`として新規ルートを追加
   - 既存の`/commits/aggregation`とは別のルート

### Research Items
1. **Radix UI Checkbox**: `@radix-ui/react-checkbox`の使用方法と既存のUIコンポーネントパターンとの整合性
2. **複数選択のクエリパラメータ**: Inertia.jsでの複数選択値のクエリパラメータ送信方法（配列形式）

### Testing Strategy
- **Backend**: Feature Tests for `UserProductivityController`, Unit Tests for `UserProductivityShowResponse`の集計ロジック
- **Frontend**: Component Tests for チェックボックスコンポーネント、Page Tests for ユーザー生産性ページ
- **Integration**: 既存の`CommitUserMonthlyAggregation`データを使用した統合テスト
