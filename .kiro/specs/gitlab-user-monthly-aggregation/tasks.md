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

- [ ] 1. UserInfoエンティティを作成する
- [x] 1.1 UserInfoエンティティを実装する
  - `app/Domain/UserInfo.php`に`readonly class UserInfo`を作成
  - `ComparesProperties`トレイトを使用
  - `AuthorEmail`と`AuthorName`のValue Objectsをプロパティとして持つ
  - コンストラクタで`AuthorEmail $email`と`AuthorName $name`を受け取る
  - 既存の`Project`エンティティパターンに従う
  - _Requirements: 7.1,7.2,7.3_

- [x] 1.2 UserInfoエンティティのテストを作成する
  - `tests/Unit/Domain/UserInfoTest.php`を作成
  - エンティティの作成とプロパティアクセスを検証
  - `equals()`メソッドの動作を検証
  - _Requirements: 7.1,7.2,7.3_

- [ ] 2. リポジトリインターフェースを拡張する
- [x] 2.1 CommitUserMonthlyAggregationRepositoryインターフェースに新規メソッドを追加する
  - `findAllUsers(): Collection<int, UserInfo>`メソッドを追加
  - `findAvailableYears(): Collection<int, int>`メソッドを追加
  - `findByUsersAndYear(array $authorEmails, ?int $year): Collection<int, CommitUserMonthlyAggregation>`メソッドを追加
  - PHPDocでパラメータと戻り値の型を明記
  - 空配列`[]`の場合は全ユーザー取得、nullは使用しないことを明記
  - _Requirements: 1.1,1.2,1.3,6.1,6.2,6.3,7.1,7.2,7.3,8.1,8.2,8.3_

- [ ] 3. リポジトリ実装を拡張する
- [x] 3.1 EloquentCommitUserMonthlyAggregationRepositoryにfindAllUsers()メソッドを実装する
  - `SELECT DISTINCT author_email, author_name FROM commit_user_monthly_aggregations ORDER BY author_name`で取得
  - 取得したデータを`UserInfo`エンティティに変換
  - `AuthorEmail`と`AuthorName`のValue Objectsを使用して`UserInfo`を構築
  - ユーザー名がnullの場合は`AuthorName(null)`を使用
  - _Requirements: 7.1,7.2,7.3,8.1,8.2,8.3_

- [x] 3.2 EloquentCommitUserMonthlyAggregationRepositoryにfindAvailableYears()メソッドを実装する
  - `SELECT DISTINCT year FROM commit_user_monthly_aggregations ORDER BY year`で取得
  - 年のコレクションを昇順でソートして返却
  - データが存在しない場合は空のコレクションを返却
  - _Requirements: 6.1,6.2,6.3,8.1,8.2,8.3_

- [x] 3.3 EloquentCommitUserMonthlyAggregationRepositoryにfindByUsersAndYear()メソッドを実装する
  - プロジェクト・ブランチの条件は指定しない（全リポジトリから取得）
  - `$authorEmails`が空配列`[]`の場合は全ユーザーを取得（フィルタリングなし）
  - `$authorEmails`が配列の場合は`WHERE author_email IN (...)`でフィルタリング
  - `$year`がnullの場合は全年を取得（フィルタリングなし）
  - `$year`が指定されている場合は`WHERE year = ?`でフィルタリング
  - `CommitUserMonthlyAggregation`エンティティのコレクションを返却
  - _Requirements: 1.1,1.2,1.3,1.4,8.1,8.2,8.3_

- [x] 3.4 リポジトリ実装のテストを作成する
  - `tests/Feature/Infrastructure/EloquentCommitUserMonthlyAggregationRepositoryTest.php`に新規メソッドのテストを追加
  - `findAllUsers()`のテスト：ユーザー一覧が正しく取得され、`UserInfo`エンティティに変換されることを検証
  - `findAvailableYears()`のテスト：年一覧が正しく取得され、昇順でソートされることを検証
  - `findByUsersAndYear()`のテスト：フィルタリングが正しく動作することを検証（空配列、年フィルター、ユーザーフィルター）
  - _Requirements: 1.1,1.2,1.3,6.1,6.2,6.3,7.1,7.2,7.3,8.1,8.2,8.3_

- [ ] 4. プレゼンテーション層のコンポーネントを作成する
- [x] 4.1 UserProductivityShowRequestを作成する
  - `app/Presentation/Request/Commit/UserProductivityShowRequest.php`を作成
  - `BaseRequest`を継承
  - バリデーションルール：`year` (nullable, integer, min:1, max:9999), `users` (nullable, array), `users.*` (string, email)
  - `getYear(): ?int`メソッドを実装
  - `getUsers(): array<string>`メソッドを実装（空配列を返す場合も考慮）
  - 既存の`AggregationShowRequest`パターンに従う
  - _Requirements: 1.2,1.3,5.1,5.2_

- [x] 4.2 UserProductivityShowResponseを作成する
  - `app/Presentation/Response/Commit/UserProductivityShowResponse.php`を作成
  - `CommitUserMonthlyAggregation`コレクションから表示用データへの変換を担当
  - `buildChartData()`メソッド：月ごと、ユーザーごとにグループ化してグラフ用データを構築
  - `buildTableData()`メソッド：月ごとの合計行数（追加行数+削除行数）を表用データとして構築
  - ユーザーキーは`author_email`のみを使用（既存は`project_id-branch_name-author_email`）
  - 複数リポジトリにまたがる同一ユーザーのデータを統合（月ごとに`total_additions`、`total_deletions`、`commit_count`を合計）
  - `toArray()`メソッド：Inertia.jsに渡すための配列に変換
  - 既存の`AggregationShowResponse`パターンに従う
  - _Requirements: 1.4,2.1,2.2,2.3,2.4,2.5,3.1,3.2,3.3,3.4,3.5,3.6,4.1,4.2,4.3,4.4,4.5,4.6_

- [x] 4.3 UserProductivityControllerを作成する
  - `app/Presentation/Controller/UserProductivityController.php`を作成
  - `BaseController`を継承
  - `show()`メソッドを実装
  - `UserProductivityShowRequest`でバリデーション
  - `CommitUserMonthlyAggregationRepository`からデータ取得（`findAllUsers()`, `findAvailableYears()`, `findByUsersAndYear()`）
  - `UserProductivityShowResponse`でデータ変換
  - `BaseController::renderWithErrorHandling`を使用してエラーハンドリング
  - Inertia.jsで`Commit/UserProductivity`ページをレンダリング
  - 既存の`CommitController::aggregationShow`メソッドを参考
  - _Requirements: 1.1,1.2,1.3,1.4,5.1,5.2,5.3,5.4,5.5,5.6,5.7,6.1,6.2,6.3,7.1,7.2,7.3_

- [x] 4.4 プレゼンテーション層のテストを作成する
  - `tests/Feature/Presentation/Controller/UserProductivityControllerTest.php`を作成
  - `show()`メソッドのテスト：コントローラーからレスポンスまでの統合フローを検証
  - 年フィルターとユーザーフィルターが正しく動作することを検証
  - 複数リポジトリにまたがる同一ユーザーのデータが正しく統合表示されることを検証
  - `UserProductivityShowRequest`のバリデーションルールを検証
  - `UserProductivityShowResponse`の`buildChartData()`と`buildTableData()`を検証
  - _Requirements: 1.1,1.2,1.3,1.4,2.1,2.2,2.3,2.4,2.5,5.1,5.2,5.3,5.4,5.5,5.6,5.7,6.1,6.2,6.3,7.1,7.2,7.3_

- [ ] 5. ルート定義を追加する
- [x] 5.1 ユーザー生産性画面のルートを追加する
  - `routes/web.php`に`GET /commits/user-productivity`ルートを追加
  - `UserProductivityController::show`メソッドにマッピング
  - ルート名：`commits.user-productivity`
  - 既存のルート定義パターンに従う
  - _Requirements: 1.1,5.1,5.2,5.3,5.4,5.5,5.6,5.7_

- [ ] 6. フロントエンドの型定義を追加する
- [x] 6.1 UserProductivityPageProps型定義を追加する
  - `resources/js/types/user.d.ts`を作成
  - `UserProductivityPageProps`インターフェースを定義
  - `years: number[]`, `users: Array<{ author_email: string; author_name: string | null }>`, `selectedYear?: number`, `selectedUsers?: string[]`, `chartData`, `tableData`, `userNames`などのプロパティを定義
  - 既存の`AggregationPageProps`（`resources/js/types/commit.d.ts`）を参考
  - _Requirements: 3.1,3.2,3.3,3.4,3.5,3.6,4.1,4.2,4.3,4.4,4.5,4.6,5.1,5.2,5.3,5.4,5.5,5.6,5.7,7.4,7.5,7.6,7.7_

- [ ] 7. UIコンポーネントを追加する
- [x] 7.1 Checkboxコンポーネントを追加する
  - `npx shadcn@latest add checkbox`を実行
  - `components.json`の設定に基づいて`resources/js/components/ui/checkbox.tsx`が作成される
  - 既存のUIコンポーネント（`select.tsx`, `button.tsx`）と同じパターンで実装される
  - プロジェクトのデザインシステム（Tailwind CSS、`cn()`関数）に自動統合される
  - _Requirements: 7.4,7.5,7.6,7.7_

- [ ] 8. フロントエンドページコンポーネントを作成する
- [x] 8.1 UserProductivityPageコンポーネントを作成する
  - `resources/js/pages/Commit/UserProductivity.tsx`を作成
  - Inertia.jsの`usePage()`でpropsを取得
  - 年フィルターは`Select`コンポーネントを使用（既存パターン）
  - ユーザー複数選択は`Checkbox`コンポーネントを使用
  - グラフ表示はRechartsを使用（既存の`Commit/Aggregation.tsx`を参考）
  - 表表示はTable UIコンポーネントを使用（既存パターン）
  - フィルター変更時は`router.get(url, { query: { year, users }, preserveState: true, preserveScroll: true })`で更新
  - 空状態メッセージを表示（データが存在しない場合）
  - 既存の`Commit/Aggregation.tsx`パターンに従う
  - _Requirements: 3.1,3.2,3.3,3.4,3.5,3.6,4.1,4.2,4.3,4.4,4.5,4.6,5.1,5.2,5.3,5.4,5.5,5.6,5.7,7.4,7.5,7.6,7.7_

- [x] 8.2 UserProductivityPageのStorybookストーリーを作成する
  - `stories/Commit/UserProductivity.stories.tsx`を作成
  - 正常状態、空状態、ローディング状態、エラー状態などのストーリーを定義
  - Inertia.jsのモックを設定（`stories/mocks/inertia.tsx`を使用）
  - 既存の`stories/Commit/Aggregation.stories.tsx`を参考
  - _Requirements: 3.1,3.2,3.3,3.4,3.5,3.6,4.1,4.2,4.3,4.4,4.5,4.6,5.1,5.2,5.3,5.4,5.5,5.6,5.7,7.4,7.5,7.6,7.7_

- [ ] 9. ドキュメント更新と最終確認
- [x] 9.1 README.mdを更新する
  - 既存のREADME.mdの構造に従う
  - ユーザー生産性機能の説明を追加（任意）

- [x] 9.2 最終確認として`npm run pre-push`を実行する
  - コード品質チェック（lint、型チェック等）を実施する
  - すべてのチェックが通過することを確認する
  - 問題がある場合は修正してから再実行する
