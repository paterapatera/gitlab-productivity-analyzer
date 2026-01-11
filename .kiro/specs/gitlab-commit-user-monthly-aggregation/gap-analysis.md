# Gap Analysis: GitLab Commit User Monthly Aggregation

## 分析概要

本機能は、GitLabのコミットデータからユーザーごとの月次集計を生成し、永続化・表示する機能です。既存のコミット収集機能を拡張し、集計データの管理と可視化を追加します。

### 主要な発見事項

- **既存の強み**: コミットデータは既に永続化されており、クリーンアーキテクチャのパターンが確立されている
- **主要なギャップ**: 月次集計テーブル、集計サービス、集計データ取得リポジトリ、グラフ表示UIが未実装
- **統合ポイント**: `CollectCommits`サービスの完了後に集計処理を自動実行
- **技術的課題**: グラフライブラリの選定と導入が必要

## 1. 現在の状態調査

### 1.1 既存のドメイン関連アセット

#### データモデル
- **`commits`テーブル**: 既に存在
  - `project_id`, `branch_name`, `sha` (複合プライマリキー)
  - `author_name`, `author_email` (ユーザー識別用)
  - `additions`, `deletions` (集計対象データ)
  - `committed_date` (年月抽出用)
- **`commit_collection_histories`テーブル**: 既に存在
  - 収集履歴を管理（最終集計月の判定に利用可能）

#### 既存サービス
- **`CollectCommits`サービス** (`app/Application/Service/CollectCommits.php`)
  - コミットを収集・永続化
  - `BaseService`を継承し、トランザクション管理を利用
  - 収集完了後に履歴を更新

#### 既存リポジトリ
- **`CommitRepository`** (`app/Application/Port/CommitRepository.php`)
  - コミットの保存・取得機能
  - `EloquentCommitRepository`が実装

#### 既存コントローラー
- **`CommitController`** (`app/Presentation/Controller/CommitController.php`)
  - `collectShow()`, `collect()`, `recollectShow()`, `recollect()` メソッド
  - Inertia.jsを使用してReactページをレンダリング

### 1.2 アーキテクチャパターンと制約

#### クリーンアーキテクチャ
- **ドメイン層** (`app/Domain/`): エンティティ、値オブジェクト
- **アプリケーション層** (`app/Application/`): サービス、DTO、Port/Contract
- **インフラストラクチャ層** (`app/Infrastructure/`): リポジトリ実装、Eloquentモデル
- **プレゼンテーション層** (`app/Presentation/`): コントローラー、リクエスト/レスポンス

#### パターン
- **BaseServiceパターン**: トランザクション管理とエラーハンドリング
- **リポジトリパターン**: PortインターフェースとEloquent実装の分離
- **ConvertsBetweenEntityAndModelトレイト**: エンティティとEloquentモデルの変換

### 1.3 統合ポイント

- **データフロー**: `CollectCommits` → `CommitRepository` → `commits`テーブル
- **イベントフロー**: コミット収集完了後に集計処理を実行する必要がある
- **UI統合**: `CommitController`に集計画面を追加

## 2. 要件の実現可能性分析

### 2.1 技術的要件

#### データモデル要件
- ✅ **既存**: `commits`テーブルに必要なデータが存在
- ❌ **不足**: 月次集計テーブル（`commit_user_monthly_aggregations`など）
  - カラム: `project_id`, `branch_name`, `author_email`, `year`, `month`, `author_name`, `total_additions`, `total_deletions`, `commit_count`
  - 複合プライマリキー: `(project_id, branch_name, author_email, year, month)`

#### サービス要件
- ✅ **既存**: `BaseService`パターン、トランザクション管理
- ❌ **不足**: 集計生成サービス (`AggregateCommits`など)
  - コミットデータから月次集計を生成
  - 最終集計月から先月までのデータを集計
  - 既存集計月をスキップ

#### リポジトリ要件
- ✅ **既存**: `CommitRepository`でコミットデータを取得可能
- ❌ **不足**: 集計データリポジトリ (`CommitUserMonthlyAggregationRepository`)
  - 集計データの保存・取得
  - 最終集計月の取得

#### UI要件
- ✅ **既存**: Inertia.js、React、Tailwind CSS、Radix UI
- ❌ **不足**: グラフライブラリ（Recharts、Chart.js、Victoryなど）
- ❌ **不足**: 集計画面コンポーネント (`Commit/Aggregation.tsx`)

### 2.2 ビジネスルール要件

#### 集計ロジック
- **年月抽出**: `committed_date`から年月を抽出（タイムゾーン考慮）
- **ユーザー識別**: `author_email`で識別、`author_name`を保存
- **集計範囲**: 最終集計月から先月まで（今月は除外）
- **重複防止**: 同一キーの集計データは更新（新規作成しない）

#### 自動実行タイミング
- **トリガー**: `CollectCommits`サービスの完了後
- **対象**: 収集されたプロジェクト・ブランチ

### 2.3 非機能要件

#### パフォーマンス
- **集計処理**: 大量のコミットデータを効率的に集計
- **クエリ最適化**: 年月、プロジェクト、ブランチ、ユーザーでのインデックス

#### 信頼性
- **トランザクション**: 集計処理はトランザクション内で実行
- **エラーハンドリング**: 集計失敗がコミット保存を妨げない

## 3. 実装アプローチの選択肢

### Option A: 既存コンポーネントの拡張

#### 拡張対象
- **`CollectCommits`サービス**: 集計処理を追加
- **`CommitController`**: 集計画面のメソッドを追加

#### 互換性評価
- ✅ `CollectCommits`のインターフェースは変更不要（内部処理の追加）
- ✅ 既存の呼び出し元への影響なし
- ⚠️ サービスの責任が増加（単一責任原則の懸念）

#### 複雑性と保守性
- ⚠️ `CollectCommits`が肥大化する可能性
- ⚠️ 集計ロジックと収集ロジックが混在

**トレードオフ**:
- ✅ 最小限の新規ファイル、初期開発が速い
- ✅ 既存パターンとインフラを活用
- ❌ 既存コンポーネントの肥大化リスク
- ❌ 既存ロジックの複雑化

### Option B: 新規コンポーネントの作成（推奨）

#### 新規作成の根拠
- **明確な責任分離**: 集計機能は収集機能とは独立した責任
- **既存コンポーネントの複雑度**: `CollectCommits`は既に十分な機能を持っている
- **テスト容易性**: 集計ロジックを独立してテスト可能

#### 新規コンポーネント
- **ドメイン層**:
  - `CommitUserMonthlyAggregation` エンティティ
  - 値オブジェクト: `AggregationYear`, `AggregationMonth`
- **アプリケーション層**:
  - `AggregateCommits` サービス（Contract + Service）
  - `CommitUserMonthlyAggregationRepository` Port
  - DTO: `AggregateCommitsResult`
- **インフラストラクチャ層**:
  - `EloquentCommitUserMonthlyAggregationRepository`
  - `CommitUserMonthlyAggregationEloquentModel`
- **プレゼンテーション層**:
  - `CommitController::aggregationShow()`, `aggregationShow()` メソッド
  - リクエスト/レスポンスクラス

#### 統合ポイント
- **`CollectCommits`サービス**: 完了後に`AggregateCommits`を呼び出し
- **`CommitController`**: 集計画面のルーティングとデータ取得

#### 責任境界
- **`AggregateCommits`**: コミットデータから月次集計を生成・保存
- **`CollectCommits`**: コミットの収集・永続化（変更なし）
- **`CommitController`**: HTTPリクエストの処理とInertia.jsレスポンス

**トレードオフ**:
- ✅ 責任の明確な分離
- ✅ 独立したテストが容易
- ✅ 既存コンポーネントの複雑度を抑制
- ❌ ファイル数の増加
- ❌ インターフェース設計の注意が必要

### Option C: ハイブリッドアプローチ

#### 組み合わせ戦略
- **新規作成**: 集計サービス、集計リポジトリ、集計エンティティ
- **拡張**: `CollectCommits`サービスに集計呼び出しを追加（軽微な変更）
- **新規作成**: 集計画面UI

#### 段階的実装
- **Phase 1**: 集計サービスとリポジトリの実装
- **Phase 2**: `CollectCommits`への統合
- **Phase 3**: UI実装

#### リスク軽減
- 集計処理は独立してテスト可能
- 既存機能への影響を最小化
- 段階的なロールアウトが可能

**トレードオフ**:
- ✅ 複雑な機能に適したバランス
- ✅ 反復的な改善が可能
- ❌ より複雑な計画が必要
- ❌ 適切に調整しないと一貫性が損なわれる可能性

## 4. 要件とアセットのマッピング

### Requirement 1: 月次集計データの永続化

| 要件 | 状態 | アセット/ギャップ |
|------|------|------------------|
| 集計テーブルの作成 | ❌ Missing | `commit_user_monthly_aggregations`テーブル（マイグレーション） |
| 集計エンティティ | ❌ Missing | `CommitUserMonthlyAggregation`エンティティ |
| 集計リポジトリ | ❌ Missing | `CommitUserMonthlyAggregationRepository` Port + 実装 |
| 値オブジェクト | ❌ Missing | `AggregationYear`, `AggregationMonth` |

### Requirement 2: コミットデータからの集計生成

| 要件 | 状態 | アセット/ギャップ |
|------|------|------------------|
| 集計サービス | ❌ Missing | `AggregateCommits`サービス（Contract + Service） |
| コミットデータ取得 | ✅ Existing | `CommitRepository`を利用可能 |
| 年月抽出ロジック | ❌ Missing | 集計サービス内で実装 |
| 最終集計月の取得 | ❌ Missing | 集計リポジトリに`findLatestAggregationMonth()`メソッド追加 |
| 集計範囲の判定 | ❌ Missing | 集計サービス内で実装（先月まで、既存月をスキップ） |

### Requirement 3: 集計データの取得

| 要件 | 状態 | アセット/ギャップ |
|------|------|------------------|
| 集計データ取得メソッド | ❌ Missing | 集計リポジトリに`findByProjectAndBranch()`など追加 |
| フィルタリング機能 | ❌ Missing | 年月範囲、ユーザーでのフィルタリング |

### Requirement 4: 集計の自動更新

| 要件 | 状態 | アセット/ギャップ |
|------|------|------------------|
| 自動実行トリガー | ❌ Missing | `CollectCommits`サービスに集計呼び出しを追加 |
| エラーハンドリング | ✅ Existing | `BaseService`のエラーハンドリングパターンを利用 |

### Requirement 5: 集計データの画面表示

| 要件 | 状態 | アセット/ギャップ |
|------|------|------------------|
| グラフライブラリ | ❌ Missing | Recharts、Chart.js、Victoryなどの選定と導入 |
| 集計画面コンポーネント | ❌ Missing | `Commit/Aggregation.tsx` |
| コントローラーメソッド | ❌ Missing | `CommitController::aggregationShow()` |
| セレクトボックス | ✅ Existing | Radix UIの`Select`コンポーネントを利用可能 |
| 表コンポーネント | ✅ Existing | Radix UIまたはカスタムテーブルコンポーネント |

## 5. 実装の複雑度とリスク

### 実装工数: **M (3-7日)**

**根拠**:
- 既存パターンに従った実装（クリーンアーキテクチャ、BaseService、リポジトリパターン）
- 新規コンポーネントの作成が必要だが、既存パターンを踏襲
- グラフライブラリの選定と統合が必要
- UI実装（グラフ、表、セレクトボックス）が必要

### リスク: **Medium**

**根拠**:
- **既知のパターン**: クリーンアーキテクチャ、BaseService、リポジトリパターンは既に確立
- **新規技術要素**: グラフライブラリの選定と統合（Recharts、Chart.jsなどは一般的）
- **統合の複雑度**: `CollectCommits`への統合は軽微な変更
- **パフォーマンス**: 大量データの集計処理（インデックス設計で対応可能）

**リスク要因**:
- グラフライブラリの選定と学習コスト
- 大量データの集計処理のパフォーマンス
- タイムゾーン処理の正確性

## 6. 設計フェーズへの推奨事項

### 推奨アプローチ: **Option B (新規コンポーネントの作成)**

**理由**:
1. **責任の明確な分離**: 集計機能は収集機能とは独立した責任を持つ
2. **テスト容易性**: 集計ロジックを独立してテスト可能
3. **保守性**: 既存コンポーネントの複雑度を抑制
4. **拡張性**: 将来的な集計機能の拡張が容易

### 主要な設計決定事項

1. **集計テーブル設計**:
   - 複合プライマリキー: `(project_id, branch_name, author_email, year, month)`
   - インデックス: `project_id`, `branch_name`, `(year, month)`の組み合わせ

2. **集計サービスの設計**:
   - `AggregateCommits`サービスを`BaseService`を継承
   - 最終集計月の取得と集計範囲の判定ロジック
   - 先月までのデータのみを集計（今月は除外）

3. **統合ポイント**:
   - `CollectCommits`サービスの完了後に`AggregateCommits`を呼び出し
   - 集計失敗がコミット保存を妨げない（エラーログのみ記録）

4. **グラフライブラリの選定**:
   - **Research Needed**: Recharts、Chart.js、Victoryなどの比較
   - React 19との互換性、TypeScript対応、Tailwind CSSとの統合を考慮

5. **UI設計**:
   - 積み上げ棒グラフ（追加行・削除行）
   - 集合縦棒グラフ（複数ユーザー）
   - 表形式での表示（ユーザー×月）

### 研究が必要な項目

1. **グラフライブラリの選定**:
   - Recharts、Chart.js、Victory、Nivoなどの比較
   - React 19、TypeScript、Tailwind CSSとの統合性
   - パフォーマンス、バンドルサイズ、アクセシビリティ

2. **タイムゾーン処理**:
   - Laravelのタイムゾーン設定との整合性
   - 年月抽出時のタイムゾーン考慮方法

3. **パフォーマンス最適化**:
   - 大量データの集計処理の最適化方法
   - データベースクエリの最適化（インデックス設計）

## 7. 次のステップ

1. **設計フェーズ**: `/kiro/spec-design gitlab-commit-user-monthly-aggregation` を実行
2. **技術調査**: グラフライブラリの選定と評価
3. **実装計画**: 段階的な実装アプローチの詳細化
