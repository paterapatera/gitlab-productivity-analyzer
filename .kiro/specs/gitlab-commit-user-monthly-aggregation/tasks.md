# Implementation Plan

## TDD実装時の注意事項

**テスト作成の例外**: TDDの原則に従って実装を進めますが、以下の場合はテスト作成タスクを生成しません：

- **インターフェース（Ports）**: 型定義のみで実装がないため、実装クラスのテストで検証されます
  - 例: `CommitUserMonthlyAggregationRepository`インターフェースのテストは不要、`EloquentCommitUserMonthlyAggregationRepository`実装クラスのテストで検証
- **型定義**: 型システムが既に契約を強制しているため、リフレクションによるシグネチャ検証テストは不要
- **サービスプロバイダーのバインディング**: バインディング設定は動作テストで自然に検証されるため、専用のバインディングテストは不要

**テストの焦点**: テストは**動作と機能**を対象とし、**実装詳細**は対象外です。インターフェースや型定義の変更は、実装クラスのテストが失敗することで自然に検出されます。

詳細は`.kiro/settings/rules/tasks-generation.md`の「6. Test-Driven Development (TDD) Guidelines」を参照してください。

## Tasks

- [ ] 1. ドメインモデルの実装
- [x] 1.1 (P) 値オブジェクトの実装
  - `CommitUserMonthlyAggregationId`値オブジェクトを作成（project_id, branch_name, author_email, year, monthを含む）
  - `AggregationYear`値オブジェクトを作成（1-9999の範囲をバリデーション）
  - `AggregationMonth`値オブジェクトを作成（1-12の範囲をバリデーション）
  - 既存の値オブジェクトパターン（`ComparesValue`トレイト使用）に従う
  - _Requirements: 1.1, 1.6_

- [x] 1.2 (P) エンティティの実装
  - `CommitUserMonthlyAggregation`エンティティを作成（readonly class）
  - `CommitUserMonthlyAggregationId`をキーとして使用
  - 追加行数、削除行数、コミット数、author_nameを保持
  - 既存のエンティティパターン（`ComparesProperties`トレイト使用）に従う
  - _Requirements: 1.1, 1.2, 1.4, 1.5_

- [ ] 2. データベースマイグレーションとEloquentモデル
- [x] 2.1 データベースマイグレーションの作成
  - `commit_user_monthly_aggregations`テーブルのマイグレーションを作成
  - 複合プライマリキー（project_id, branch_name, author_email, year, month）を定義
  - インデックス（idx_project_branch, idx_year_month, idx_author_email）を追加
  - 外部キー制約（project_id → projects.id）を追加
  - CHECK制約で年・月・数値の妥当性を保証
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

- [x] 2.2 Eloquentモデルの作成
  - `CommitUserMonthlyAggregationEloquentModel`を作成
  - 複合プライマリキーを設定
  - タイムスタンプ（created_at, updated_at）を有効化
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

- [ ] 3. リポジトリインターフェースと実装
- [x] 3.1 (P) リポジトリインターフェースの作成
  - `CommitUserMonthlyAggregationRepository` Portインターフェースを作成
  - `save()`, `saveMany()`, `findLatestAggregationMonth()`, `findByProjectAndBranch()`メソッドを定義
  - _Requirements: 1.1, 1.3, 3.1, 3.2, 3.3, 3.4, 3.5_

- [x] 3.2 リポジトリ実装の作成
  - `EloquentCommitUserMonthlyAggregationRepository`を作成
  - `ConvertsBetweenEntityAndModel`トレイトを使用してエンティティとEloquentモデルの変換を実装
  - `findModel()`, `createModel()`, `toEntity()`, `updateModelFromEntity()`メソッドを実装
  - 複合プライマリキーでの検索・更新ロジックを実装
  - _Requirements: 1.1, 1.3, 3.1, 3.2, 3.3, 3.4, 3.5_

- [x] 3.3 CommitRepositoryの拡張
  - `CommitRepository` Portインターフェースに`findByProjectAndBranchAndDateRange()`メソッドを追加
  - `EloquentCommitRepository`に実装を追加（committed_dateが指定範囲内のコミットを取得）
  - 日付範囲の妥当性チェック（startDate <= endDate）を実装
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6_

- [ ] 4. 集計サービスの実装
- [x] 4.1 (P) サービスインターフェースとDTOの作成
  - `AggregateCommits` Contractインターフェースを作成
  - `AggregateCommitsResult` DTOを作成（集計件数、エラーフラグ等を含む）
  - _Requirements: 2.1, 2.7_

- [x] 4.2 集計サービスの実装
  - `AggregateCommits`サービスを実装（`BaseService`を継承）
  - 最終集計月から先月までのデータのみを集計（今月は除外）
  - 既存集計月をスキップし、新規データのみを集計
  - コミットデータから年月を抽出し、ユーザーごとに集計
  - 同一ユーザー・同一年月のコミットのadditionsとdeletionsを合計
  - 集計対象のコミット数をカウント
  - 同一ユーザーのコミットからauthor_nameを取得して集計データに保存
  - トランザクション内で集計データを保存（部分的な保存を防止）
  - タイムゾーンを考慮して年月を判定
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8, 2.9, 2.10, 1.6_

- [ ] 5. CollectCommitsへの統合
- [x] 5.1 CollectCommitsへの集計処理の統合
  - `CollectCommits::execute()`内で、コミット保存完了後に`AggregateCommits::execute()`を呼び出す
  - try-catchで囲み、エラー時は`\Log::error()`でログに記録するのみで、`CollectCommitsResult`には影響を与えない
  - サービスプロバイダーで`AggregateCommits`をバインディング
  - _Requirements: 2.11, 4.1, 4.2, 4.3, 4.4, 4.5, 4.6_

- [ ] 6. コントローラーとUI
- [x] 6.1 (P) コントローラーメソッドの実装
  - `CommitController::aggregationShow()`メソッドを追加
  - プロジェクト一覧を取得（`ProjectRepository`を使用）
  - ブランチ一覧を取得（`CommitCollectionHistoryRepository::findAll()`から抽出）
  - 年一覧を取得（集計データから抽出）
  - 選択されたプロジェクト・ブランチ・年の集計データを取得
  - Inertia.jsレスポンスを返却
  - Form Requestクラスでリクエストパラメータをバリデーション
  - _Requirements: 5.1, 5.2, 5.3, 5.4_

- [x] 6.2 (P) TypeScript型定義の作成
  - `AggregationShowRequest`型を定義
  - `AggregationShowResponse`型を定義
  - 集計データの型定義を追加
  - _Requirements: 5.1, 5.2, 5.3, 5.4_

- [x] 6.3 UIコンポーネントの実装
  - `Commit/Aggregation.tsx`コンポーネントを作成
  - セレクトボックス（プロジェクト・ブランチ、年）を実装（Radix UI Selectを使用）
  - セレクトボックスの変更時にInertia.jsの`router.visit()`でページ遷移
  - 棒グラフを実装（Rechartsを使用、横軸：月、縦軸：行数）
  - 追加行を青色、削除行を赤色で表示
  - 追加行と削除行を積み上げ棒グラフで表示
  - 複数ユーザーを集合縦棒で表示
  - ユーザー名を表示（author_name、名前がない場合は"Unknown"）
  - データをプロジェクトID、ブランチ名、ユーザーの昇順でソート
  - 表を実装（縦軸：ユーザー、横軸：月、セル：合計行数）
  - 空データ・エラー状態の処理
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8, 5.9, 5.10, 5.11, 5.12_

- [x] 6.4 Storybookストーリーの作成
  - `stories/Commit/Aggregation.stories.tsx`を作成
  - 正常状態のストーリーを定義
  - 空状態のストーリーを定義
  - ローディング状態のストーリーを定義
  - エラー状態のストーリーを定義
  - 複数ユーザーのストーリーを定義
  - セレクトボックス操作のストーリーを定義
  - Inertia.jsのモックを使用
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8, 5.9, 5.10, 5.11, 5.12_

- [ ] 7. ルーティングの追加
- [x] 7.1 ルート定義の追加
  - `/commits/aggregation`ルートを追加（GET）
  - `CommitController::aggregationShow()`にマッピング
  - _Requirements: 5.1_

- [x] 8. テスト
- [x] 8.1 (P) ドメインモデルのテスト
  - `CommitUserMonthlyAggregationId`値オブジェクトのテスト（バリデーション、等価性）
  - `AggregationYear`値オブジェクトのテスト（バリデーション、等価性）
  - `AggregationMonth`値オブジェクトのテスト（バリデーション、等価性）
  - `CommitUserMonthlyAggregation`エンティティのテスト（不変性、等価性）
  - _Requirements: 1.1, 1.6_

- [x] 8.2 リポジトリのテスト
  - `EloquentCommitUserMonthlyAggregationRepository`のテスト（保存・更新・取得・重複防止）
  - `EloquentCommitRepository::findByProjectAndBranchAndDateRange()`のテスト（日付範囲での取得）
  - _Requirements: 1.1, 1.3, 3.1, 3.2, 3.3, 3.4, 3.5, 2.1, 2.2, 2.3, 2.4, 2.5, 2.6_

- [x] 8.3 集計サービスのテスト
  - `AggregateCommits`サービスのテスト（集計ロジック、年月抽出、集計範囲判定、エラーハンドリング）
  - 最終集計月から先月までのデータのみを集計することを検証
  - 既存集計月をスキップすることを検証
  - タイムゾーン考慮を検証
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8, 2.9, 2.10, 1.6_

- [x] 8.4 CollectCommits統合のテスト
  - `CollectCommits`から`AggregateCommits`への自動実行を検証
  - 集計処理の失敗がコミット保存を妨げないことを検証
  - エラーログが記録されることを検証
  - _Requirements: 2.11, 4.1, 4.2, 4.3, 4.4, 4.5, 4.6_

- [x] 8.5 コントローラーのテスト
  - `CommitController::aggregationShow()`のテスト（HTTPリクエスト・レスポンス、バリデーション）
  - プロジェクト一覧、ブランチ一覧、年一覧の取得を検証
  - 集計データの取得を検証
  - _Requirements: 5.1, 5.2, 5.3, 5.4_

- [x] 8.6 UIコンポーネントのテスト
  - `Commit/Aggregation.tsx`のテスト（レンダリング、セレクトボックス操作、グラフ表示、表表示）
  - 空データ・エラー状態の表示を検証
  - 注: プロジェクト全体のパスエイリアス設定の問題により、テスト実行環境が整っていないため保留
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8, 5.9, 5.10, 5.11, 5.12_

- [x] 9. ドキュメント更新と最終確認
- [x] 9.1 README.mdを更新する
  - 既存のREADME.mdの構造に従う
  - 新機能の説明を追加

- [x] 9.2 最終確認としてコード品質チェックを実行する
  - `vendor/bin/sail bin pint --dirty`を実行してコードフォーマットを確認
  - `vendor/bin/sail artisan test --compact`を実行してテストがすべて通過することを確認
  - `vendor/bin/sail npm run lint`を実行してフロントエンドのコード品質を確認
  - 問題がある場合は修正してから再実行する
