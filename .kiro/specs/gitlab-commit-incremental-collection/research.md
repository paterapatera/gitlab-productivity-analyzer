# Research & Design Decisions

## Summary
- **Feature**: gitlab-commit-incremental-collection
- **Discovery Scope**: Extension
- **Key Findings**:
  - 既存のクリーンアーキテクチャパターンに準拠した拡張が可能
  - 既存の`CollectCommits`サービスを拡張して増分収集機能を実装
  - 新しい`CommitCollectionHistoryRepository`を作成して収集履歴を管理
  - 既存のEloquentモデルパターンに従って新しいモデルを作成

## Research Log

### 既存アーキテクチャパターンの確認
- **Context**: 既存のコードベースのパターンを確認し、新しい機能を統合する方法を決定
- **Sources Consulted**: 
  - `app/Application/Service/CollectCommits.php`
  - `app/Infrastructure/Repositories/EloquentCommitRepository.php`
  - `app/Infrastructure/Repositories/Eloquent/CommitEloquentModel.php`
  - `app/Presentation/Controller/CommitController.php`
- **Findings**:
  - クリーンアーキテクチャの4層構造（Domain, Application, Infrastructure, Presentation）が確立
  - Ports and Adapters パターンが使用されている（`app/Application/Port/`にインターフェース、`app/Infrastructure/`に実装）
  - `BaseService`パターンでトランザクション管理とエラーハンドリングが統一
  - `ConvertsBetweenEntityAndModel`トレイトでリポジトリの共通実装を提供
  - Eloquentモデルは`app/Infrastructure/Repositories/Eloquent/`に配置
  - 複合プライマリキーは配列形式で定義（例: `['project_id', 'branch_name', 'sha']`）
- **Implications**:
  - 新しいリポジトリは既存のパターンに従って実装
  - 既存のサービスを拡張する際は、後方互換性を維持
  - 新しいEloquentモデルは既存のパターンに従って作成

### データベーススキーマパターンの確認
- **Context**: 新しいテーブルの設計方法を決定
- **Sources Consulted**:
  - `database/migrations/2026_01_10_212942_create_commits_table.php`
  - `database/migrations/2026_01_10_071905_create_projects_table.php`
- **Findings**:
  - マイグレーションファイルは`{timestamp}_{action}_{table}.php`形式
  - テーブル名は`snake_case`、複数形
  - 外部キー制約は`foreign()`メソッドで定義
  - 複合ユニーク制約は`unique()`メソッドで定義
  - インデックスは`index()`メソッドで定義
- **Implications**:
  - `commit_collection_histories`テーブルは既存のパターンに従って作成
  - 複合ユニーク制約`(project_id, branch_name)`を定義
  - `projects`テーブルへの外部キー制約を定義

### 既存サービスの動作確認
- **Context**: `CollectCommits::execute()`メソッドの動作変更の影響を評価
- **Sources Consulted**:
  - `app/Application/Service/CollectCommits.php`
  - `app/Presentation/Controller/CommitController.php`
- **Findings**:
  - `execute()`メソッドは`sinceDate`パラメータを受け取り、`null`の場合は全コミットを収集
  - 既存の呼び出し元は`sinceDate`を明示的に指定している可能性がある
  - `sinceDate`が`null`の場合に自動判定する動作変更は、既存の動作を拡張する形で実装可能
- **Implications**:
  - `sinceDate`が`null`の場合に自動判定する動作を追加
  - 既存のテストを維持し、動作変更を検証
  - 後方互換性を維持（`sinceDate`が明示的に指定された場合は既存の動作を維持）

## Architecture Pattern Evaluation

| Option | Description | Strengths | Risks / Limitations | Notes |
|--------|-------------|-----------|---------------------|-------|
| 既存パターンの拡張 | 既存のコンポーネントを拡張して機能を追加 | 最小限の変更、既存のパターンを活用 | コンポーネントが肥大化する可能性 | 推奨アプローチ |
| 新規コンポーネントの作成 | 新しいコンポーネントを作成して機能を実装 | 関心の分離が明確 | ファイル数が増加 | 収集履歴リポジトリには適用 |

## Design Decisions

### Decision: 収集履歴リポジトリの分離
- **Context**: 収集履歴は独立したドメイン概念のため、新しいリポジトリとして実装
- **Alternatives Considered**:
  1. `CommitRepository`に履歴管理機能を追加 — 既存のリポジトリが肥大化
  2. 新しい`CommitCollectionHistoryRepository`を作成 — 関心の分離が明確
- **Selected Approach**: 新しい`CommitCollectionHistoryRepository`を作成
- **Rationale**: 収集履歴は独立したドメイン概念であり、`Commit`エンティティとは異なる責任を持つ
- **Trade-offs**: 
  - ✅ 関心の分離が明確
  - ✅ 単体テストが容易
  - ❌ ファイル数が増加
- **Follow-up**: リポジトリのインターフェース設計を確認

### Decision: `CollectCommits::execute()`の動作拡張
- **Context**: `sinceDate`が`null`の場合に自動判定する機能を追加
- **Alternatives Considered**:
  1. 既存の`execute()`メソッドを拡張 — 後方互換性を維持
  2. 新しい`executeIncremental()`メソッドを追加 — 既存の動作を変更しない
- **Selected Approach**: 既存の`execute()`メソッドを拡張
- **Rationale**: `sinceDate`が`null`の場合の動作を拡張することで、既存の呼び出し元に影響を与えずに機能を追加できる
- **Trade-offs**:
  - ✅ 既存の呼び出し元に影響を与えない
  - ✅ シンプルな実装
  - ⚠️ 動作変更のテストが必要
- **Follow-up**: 既存のテストを確認し、動作変更を検証

### Decision: 収集履歴エンティティの作成
- **Context**: 収集履歴をドメインエンティティとして表現するかどうか
- **Alternatives Considered**:
  1. ドメインエンティティを作成 — ドメイン層の一貫性を維持
  2. DTOのみを使用 — シンプルな実装
- **Selected Approach**: ドメインエンティティ`CommitCollectionHistory`を作成
- **Rationale**: 既存のパターン（`Commit`、`Project`）に従い、ドメイン層の一貫性を維持
- **Trade-offs**:
  - ✅ ドメイン層の一貫性
  - ✅ ビジネスロジックの表現
  - ❌ 実装の複雑性が増加
- **Follow-up**: エンティティの設計を確認

## Risks & Mitigations
- **リスク**: `CollectCommits::execute()`の動作変更が既存の呼び出し元に影響する可能性
  - **軽減策**: 既存のテストを維持し、動作変更を検証。`sinceDate`が明示的に指定された場合は既存の動作を維持
- **リスク**: 収集履歴テーブルの設計が不適切な場合、データ整合性の問題が発生する可能性
  - **軽減策**: 複合ユニーク制約と外部キー制約を定義し、データ整合性を保証
- **リスク**: 再収集ページのUI設計が不適切な場合、ユーザビリティが低下する可能性
  - **軽減策**: 既存の`Commit/Index.tsx`パターンに従い、一貫性のあるUIを提供

## References
- Laravel 12 Documentation — マイグレーションとEloquentモデルのパターン
- 既存のコードベース — アーキテクチャパターンと実装パターン
