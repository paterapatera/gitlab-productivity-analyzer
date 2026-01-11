# Implementation Gap Analysis

## Analysis Summary

この分析では、GitLabコミット増分収集機能の実装ギャップを特定しました。主なギャップは以下の通りです：

- **データモデル**: 収集履歴テーブルとリポジトリが未実装
- **サービス層**: `CollectCommits` サービスに増分収集の自動判定と履歴記録機能が不足
- **リポジトリ層**: `CommitRepository` に最新コミット日時取得メソッドが不足
- **プレゼンテーション層**: 再収集ページとコントローラーメソッドが未実装
- **フロントエンド**: 再収集ページコンポーネントが未実装

既存のアーキテクチャパターン（クリーンアーキテクチャ、Ports and Adapters パターン）に準拠した実装が可能で、既存のコンポーネントを拡張する形で実装できます。

## Current State Investigation

### Existing Components

#### Backend Architecture
- **クリーンアーキテクチャ**: 4層構造（Domain, Application, Infrastructure, Presentation）が確立
- **Ports and Adapters パターン**: `app/Application/Port/` にインターフェース、`app/Infrastructure/` に実装
- **BaseService パターン**: トランザクション管理とエラーハンドリングの統一パターン
- **Repository パターン**: `ConvertsBetweenEntityAndModel` トレイトによる共通実装

#### Existing Commit Collection
- **`CollectCommits` サービス**: `app/Application/Service/CollectCommits.php`
  - `sinceDate` パラメータを受け取り、GitLab APIからコミットを取得
  - トランザクション管理とエラーハンドリングを実装
- **`CommitRepository` インターフェース**: `app/Application/Port/CommitRepository.php`
  - `save()` と `saveMany()` メソッドのみ
- **`EloquentCommitRepository`**: `app/Infrastructure/Repositories/EloquentCommitRepository.php`
  - `ConvertsBetweenEntityAndModel` トレイトを使用
  - 既存レコードの更新は既に実装済み（重複エラーを発生させない）

#### Existing UI
- **`CommitController`**: `app/Presentation/Controller/CommitController.php`
  - `index()`: コミット収集ページを表示
  - `collect()`: コミット収集を実行
- **`Commit/Index.tsx`**: `resources/js/pages/Commit/Index.tsx`
  - プロジェクト選択、ブランチ名入力、開始日入力フォーム

### Conventions

#### Naming Patterns
- **Ports**: `{Entity}Repository` (例: `CommitRepository`)
- **Repositories**: `Eloquent{Entity}Repository` (例: `EloquentCommitRepository`)
- **Services**: `{Action}{Entity}` (例: `CollectCommits`)
- **Controllers**: `{Entity}Controller` (例: `CommitController`)
- **Responses**: `{Action}{Entity}Response` (例: `IndexResponse`)

#### Database Patterns
- **Migrations**: `{timestamp}_{action}_{table}.php` (例: `2026_01_10_212942_create_commits_table.php`)
- **Eloquent Models**: `{Entity}EloquentModel` (例: `CommitEloquentModel`)
- **Table Names**: `snake_case`, plural (例: `commits`)
- **Primary Keys**: 複合キーの場合は `['project_id', 'branch_name', 'sha']` のような形式

#### Frontend Patterns
- **Pages**: `resources/js/pages/{Feature}/{Page}.tsx`
- **Types**: `resources/js/types/{feature}.d.ts`
- **Components**: `resources/js/components/{category}/{Component}.tsx`

## Requirements Feasibility Analysis

### Requirement 1: 最新コミット日時の取得

**Technical Needs**:
- `CommitRepository` インターフェースに `findLatestCommittedDate(ProjectId, BranchName): ?\DateTime` メソッドを追加
- `EloquentCommitRepository` に実装を追加
- `committed_date` カラムのインデックスは既に存在（`database/migrations/2026_01_10_212942_create_commits_table.php`）

**Gaps**:
- ❌ **Missing**: `CommitRepository` インターフェースにメソッドが存在しない
- ❌ **Missing**: `EloquentCommitRepository` に実装が存在しない

**Constraints**:
- 既存の `commits` テーブル構造を利用可能
- `committed_date` インデックスが既に存在するため、クエリは効率的

**Complexity**: Simple CRUD

### Requirement 2: 増分収集の自動判定

**Technical Needs**:
- `CollectCommits` サービスの `execute()` メソッドを拡張
- `CommitRepository::findLatestCommittedDate()` を呼び出して `sinceDate` を自動判定
- `sinceDate` が `null` の場合は全コミットを収集

**Gaps**:
- ❌ **Missing**: `CollectCommits` サービスに自動判定ロジックが存在しない
- ✅ **Existing**: `sinceDate` パラメータの処理は既に実装済み

**Constraints**:
- 既存の `execute()` メソッドのシグネチャを変更する必要がある（後方互換性の考慮が必要）
- または、新しいメソッド `executeIncremental()` を追加する

**Complexity**: Simple algorithmic logic

### Requirement 3: 増分収集の正確性

**Technical Needs**:
- GitLab APIの `since` パラメータの処理（既に実装済み）
- 既存レコードの更新（既に実装済み）

**Gaps**:
- ✅ **Existing**: すべての機能が既に実装済み

**Constraints**: なし

**Complexity**: N/A (既に実装済み)

### Requirement 4: エラーハンドリング

**Technical Needs**:
- `BaseService` のエラーハンドリングパターンを使用
- フォールバック動作の実装

**Gaps**:
- ✅ **Existing**: `BaseService` と `CollectCommits` のエラーハンドリングは既に実装済み
- ⚠️ **Enhancement**: 最新コミット日時取得失敗時のフォールバック動作を追加

**Constraints**: なし

**Complexity**: Simple error handling

### Requirement 5: 収集履歴の記録

**Technical Needs**:
- 新しいテーブル: `commit_collection_histories`
  - `project_id` (unsigned big integer, FK to projects)
  - `branch_name` (string, 255)
  - `latest_committed_date` (timestamp)
  - 複合ユニーク制約: `(project_id, branch_name)`
- 新しいPort: `CommitCollectionHistoryRepository`
- 新しいRepository: `EloquentCommitCollectionHistoryRepository`
- `CollectCommits` サービスに履歴記録機能を追加

**Gaps**:
- ❌ **Missing**: テーブルが存在しない
- ❌ **Missing**: Port インターフェースが存在しない
- ❌ **Missing**: Repository 実装が存在しない
- ❌ **Missing**: `CollectCommits` サービスに履歴記録機能が存在しない

**Constraints**:
- 既存の `projects` テーブルへの外部キー制約が必要
- 複合ユニーク制約により、プロジェクトとブランチの組み合わせで一意性を保証

**Complexity**: Simple CRUD + workflow

### Requirement 6: 収集履歴の取得

**Technical Needs**:
- `CommitCollectionHistoryRepository` に以下のメソッドを追加:
  - `findById(CommitCollectionHistoryId): ?CommitCollectionHistory`
  - `findAll(): Collection<int, CommitCollectionHistory>`

**Gaps**:
- ❌ **Missing**: `CommitCollectionHistoryRepository` が存在しない（Requirement 5 と統合）

**Constraints**: Requirement 5 と統合して実装

**Complexity**: Simple CRUD

### Requirement 7: 再収集ページの表示

**Technical Needs**:
- `CommitController` に `recollect()` メソッドを追加
- 新しいResponse: `RecollectResponse`
- 新しいページ: `resources/js/pages/Commit/Recollect.tsx`
- ルート: `GET /commits/recollect`
- 型定義: `resources/js/types/commit.d.ts` に `RecollectPageProps` を追加

**Gaps**:
- ❌ **Missing**: `CommitController::recollect()` メソッドが存在しない
- ❌ **Missing**: `RecollectResponse` が存在しない
- ❌ **Missing**: `Commit/Recollect.tsx` ページが存在しない
- ❌ **Missing**: ルートが存在しない
- ❌ **Missing**: 型定義が存在しない

**Constraints**:
- 既存の `CommitController` パターンに従う
- 既存の `IndexResponse` パターンに従う
- 既存の `Commit/Index.tsx` パターンに従う

**Complexity**: Simple UI + workflow

### Requirement 8: 再収集の実行

**Technical Needs**:
- `CommitController` に `recollectStore()` メソッドを追加
- 新しいRequest: `RecollectRequest`
- ルート: `POST /commits/recollect`
- `CollectCommits` サービスを呼び出して再収集を実行

**Gaps**:
- ❌ **Missing**: `CommitController::recollectStore()` メソッドが存在しない
- ❌ **Missing**: `RecollectRequest` が存在しない
- ❌ **Missing**: ルートが存在しない

**Constraints**:
- 既存の `CollectRequest` パターンに従う
- 既存の `collect()` メソッドのパターンに従う

**Complexity**: Simple workflow

## Implementation Approach Options

### Option A: Extend Existing Components

**適用範囲**:
- `CommitRepository` インターフェースと `EloquentCommitRepository` に最新コミット日時取得メソッドを追加
- `CollectCommits` サービスに増分収集の自動判定と履歴記録機能を追加
- `CommitController` に再収集メソッドを追加

**変更が必要なファイル**:
- `app/Application/Port/CommitRepository.php` - メソッド追加
- `app/Infrastructure/Repositories/EloquentCommitRepository.php` - 実装追加
- `app/Application/Service/CollectCommits.php` - ロジック追加
- `app/Presentation/Controller/CommitController.php` - メソッド追加

**互換性評価**:
- ✅ 既存のインターフェースにメソッドを追加するため、後方互換性を維持
- ✅ 既存の `execute()` メソッドのシグネチャを変更せず、内部ロジックのみ変更
- ⚠️ `CollectCommits::execute()` の動作が変更される（`sinceDate` が `null` の場合に自動判定）

**複雑性と保守性**:
- `CollectCommits` サービスが複数の責任を持つ（収集、履歴記録、自動判定）
- ファイルサイズが増加する可能性

**Trade-offs**:
- ✅ 最小限の新規ファイル、初期開発が速い
- ✅ 既存のパターンとインフラストラクチャを活用
- ❌ 既存コンポーネントが肥大化するリスク
- ❌ 既存ロジックが複雑化する可能性

### Option B: Create New Components

**適用範囲**:
- 新しい `CommitCollectionHistoryRepository` Port と実装を作成
- 新しい `RecollectCommits` サービスを作成（`CollectCommits` をラップ）
- 新しい `RecollectResponse` を作成
- 新しい `Commit/Recollect.tsx` ページを作成

**新規作成が必要なファイル**:
- `app/Application/Port/CommitCollectionHistoryRepository.php
- `app/Infrastructure/Repositories/EloquentCommitCollectionHistoryRepository.php`
- `app/Infrastructure/Repositories/Eloquent/CommitCollectionHistoryEloquentModel.php`
- `app/Application/Service/RecollectCommits.php`
- `app/Application/Contract/RecollectCommits.php`
- `app/Presentation/Response/Commit/RecollectResponse.php`
- `resources/js/pages/Commit/Recollect.tsx`
- `database/migrations/{timestamp}_create_commit_collection_histories_table.php`

**統合ポイント**:
- `RecollectCommits` サービスは `CollectCommits` サービスを使用
- `CommitController` は `RecollectCommits` サービスを呼び出し

**責任の境界**:
- `RecollectCommits`: 再収集のオーケストレーション（履歴取得、収集実行、履歴更新）
- `CollectCommits`: コミット収集の実行（既存の責任を維持）
- `CommitCollectionHistoryRepository`: 収集履歴の永続化

**Trade-offs**:
- ✅ 関心の分離が明確
- ✅ 単体テストが容易
- ✅ 既存コンポーネントの複雑性を軽減
- ❌ ファイル数が増加
- ❌ インターフェース設計に注意が必要

### Option C: Hybrid Approach

**組み合わせ戦略**:
- **拡張**: `CommitRepository` に最新コミット日時取得メソッドを追加（Option A）
- **新規作成**: `CommitCollectionHistoryRepository` と関連コンポーネントを作成（Option B）
- **拡張**: `CollectCommits` サービスに履歴記録機能を追加（Option A）
- **新規作成**: `CommitController` に再収集メソッドを追加、新しいページを作成（Option B）

**段階的実装**:
1. **Phase 1**: データモデルとリポジトリの実装
   - `CommitCollectionHistoryRepository` と実装を作成
   - マイグレーションを作成
2. **Phase 2**: サービス層の拡張
   - `CommitRepository` に最新コミット日時取得メソッドを追加
   - `CollectCommits` サービスに増分収集の自動判定と履歴記録機能を追加
3. **Phase 3**: プレゼンテーション層の実装
   - `CommitController` に再収集メソッドを追加
   - フロントエンドページを作成

**リスク軽減**:
- 段階的なロールアウト
- 既存の `CollectCommits::execute()` メソッドの動作を維持（`sinceDate` が明示的に指定された場合）
- 既存のテストを維持

**Trade-offs**:
- ✅ 複雑な機能に対してバランスの取れたアプローチ
- ✅ 反復的な改善を可能にする
- ❌ より複雑な計画が必要
- ❌ 適切に調整されないと一貫性が失われる可能性

## Recommendation

**推奨アプローチ: Option C (Hybrid Approach)**

**理由**:
1. **データモデルの分離**: 収集履歴は独立したドメイン概念のため、新しいリポジトリとして実装するのが適切
2. **既存コンポーネントの拡張**: `CommitRepository` と `CollectCommits` サービスは既存の責任を維持しつつ、必要な機能を追加
3. **段階的実装**: データモデル → サービス層 → プレゼンテーション層の順で実装することで、リスクを最小化

**主要な決定事項**:
- `CollectCommits::execute()` メソッドは `sinceDate` が `null` の場合に自動判定する（既存の動作を拡張）
- `CommitCollectionHistoryRepository` は新しいPortとして作成
- 再収集ページは新しいページコンポーネントとして作成

## Effort & Risk Assessment

### Effort: **M (3-7 days)**

**内訳**:
- データモデルとリポジトリ実装: 1-2日
- サービス層の拡張: 1-2日
- プレゼンテーション層の実装: 1-2日
- テスト作成: 1日

**根拠**:
- 既存のパターンに従うため、新しいパターンの学習は不要
- 既存のコンポーネントを拡張するため、統合は比較的簡単
- 新しいテーブルとリポジトリの実装は標準的なCRUD操作

### Risk: **Low**

**根拠**:
- 既存のパターンとアーキテクチャに準拠
- 既知の技術スタック（Laravel, Eloquent, Inertia.js, React）
- 明確なスコープと既存の実装パターン
- 既存のテストを維持しながら段階的に実装可能

**潜在的なリスク**:
- `CollectCommits::execute()` の動作変更が既存の呼び出し元に影響する可能性（`sinceDate` が `null` の場合）
  - **軽減策**: 既存のテストを維持し、動作変更を検証

## Research Needed

以下の項目は設計フェーズで詳細に検討する必要があります：

1. **`CollectCommits::execute()` の後方互換性**
   - `sinceDate` が `null` の場合の動作変更が既存の呼び出し元に影響するか
   - 既存のテストを確認し、動作変更が必要かどうかを判断

2. **収集履歴テーブルの設計**
   - 複合ユニーク制約の実装方法
   - 外部キー制約の実装方法（`projects` テーブルへの参照）

3. **再収集ページのUI設計**
   - プロジェクトとブランチの組み合わせの表示方法
   - 前回の最新コミット日時の表示形式
   - 再収集ボタンの配置とスタイリング

## Requirement-to-Asset Map

| Requirement | Existing Asset | Gap | Approach |
|------------|----------------|-----|----------|
| 1. 最新コミット日時の取得 | `CommitRepository`, `EloquentCommitRepository` | メソッドが存在しない | Extend |
| 2. 増分収集の自動判定 | `CollectCommits` | 自動判定ロジックが存在しない | Extend |
| 3. 増分収集の正確性 | `CollectCommits`, `FetchesCommits` | なし（既に実装済み） | N/A |
| 4. エラーハンドリング | `BaseService`, `CollectCommits` | フォールバック動作の追加 | Extend |
| 5. 収集履歴の記録 | なし | テーブル、Port、Repositoryが存在しない | New |
| 6. 収集履歴の取得 | なし | Repositoryが存在しない（Requirement 5と統合） | New |
| 7. 再収集ページの表示 | `CommitController`, `Commit/Index.tsx` | メソッドとページが存在しない | New |
| 8. 再収集の実行 | `CommitController`, `CollectCommits` | メソッドが存在しない | Extend |

## Next Steps

1. **設計フェーズ**: `/kiro/spec-design gitlab-commit-incremental-collection` を実行
   - データモデルの詳細設計
   - サービス層のインターフェース設計
   - UI/UX設計

2. **実装準備**:
   - 既存のテストを確認し、動作変更の影響を評価
   - データベースマイグレーションの設計
   - フロントエンドページの設計
