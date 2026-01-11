# Implementation Plan

## TDD実装時の注意事項

**テスト作成の例外**: TDDの原則に従って実装を進めますが、以下の場合はテスト作成タスクを生成しません：

- **インターフェース（Ports）**: 型定義のみで実装がないため、実装クラスのテストで検証されます
  - 例: `CommitCollectionHistoryRepository`インターフェースのテストは不要、`EloquentCommitCollectionHistoryRepository`実装クラスのテストで検証
- **型定義**: 型システムが既に契約を強制しているため、リフレクションによるシグネチャ検証テストは不要
- **サービスプロバイダーのバインディング**: バインディング設定は動作テストで自然に検証されるため、専用のバインディングテストは不要

**テストの焦点**: テストは**動作と機能**を対象とし、**実装詳細**は対象外です。インターフェースや型定義の変更は、実装クラスのテストが失敗することで自然に検出されます。

詳細は`.kiro/settings/rules/tasks-generation.md`の「6. Test-Driven Development (TDD) Guidelines」を参照してください。

## 1. データベーススキーマの作成

- [x] 1.1 (P) 収集履歴テーブルのマイグレーションを作成する
  - `commit_collection_histories`テーブルを作成する
  - `project_id`（unsigned big integer、外部キー）、`branch_name`（string 255）、`latest_committed_date`（timestamp）カラムを定義する
  - 複合プライマリキー（`project_id`, `branch_name`）を設定する
  - `project_id`にインデックスを追加する
  - `latest_committed_date`にインデックスを追加する（将来の拡張用）
  - 外部キー制約を`projects`テーブルに設定する
  - _Requirements: 5.4, 5.5_

## 2. ドメイン層の実装

- [x] 2.1 (P) CommitCollectionHistoryエンティティを作成する
  - `readonly class CommitCollectionHistory`を定義する
  - `ComparesProperties`トレイトを使用して等価性判定を実装する
  - `projectId: ProjectId`、`branchName: BranchName`、`latestCommittedDate: CommittedDate`プロパティを定義する
  - 既存の`Commit`エンティティのパターンに従う
  - _Requirements: 5.4_

## 3. アプリケーション層のポート定義

- [x] 3.1 (P) CommitCollectionHistoryRepositoryポートインターフェースを定義する
  - `save()`メソッドを定義する
  - `findById()`メソッドを定義する
  - `findAll()`メソッドを定義する
  - 適切な型ヒントとPHPDocを追加する
  - _Requirements: 5.1, 5.2, 5.3, 6.1, 6.2, 6.3_

- [x] 3.2 CommitRepositoryポートインターフェースに`findLatestCommittedDate()`メソッドを追加する
  - `ProjectId`と`BranchName`を受け取り、`?\DateTime`を返すメソッドを定義する
  - 適切な型ヒントとPHPDocを追加する
  - _Requirements: 1.1, 1.2, 1.3, 1.4_

## 4. インフラストラクチャ層の実装

- [x] 4.1 (P) CommitCollectionHistoryEloquentModelを作成する
  - Eloquentモデルを定義する
  - 複合プライマリキーを設定する
  - リレーションシップを`projects`テーブルに設定する
  - マスアサインメントの設定を行う
  - _Requirements: 5.4, 5.5_

- [x] 4.2 (P) EloquentCommitCollectionHistoryRepositoryを実装する
  - `CommitCollectionHistoryRepository`ポートを実装する
  - `ConvertsBetweenEntityAndModel`トレイトを使用する
  - `save()`メソッドを実装する（upsertパターンを使用）
  - `findById()`メソッドを実装する
  - `findAll()`メソッドを実装する
  - 既存の`EloquentProjectRepository`のパターンに従う
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 6.1, 6.2, 6.3_

- [x] 4.3 EloquentCommitRepositoryに`findLatestCommittedDate()`メソッドを実装する
  - `committed_date`インデックスを使用して効率的にクエリを実行する
  - `MAX(committed_date)`を使用して最新日時を取得する
  - コミットが存在しない場合は`null`を返す
  - 同じ日時のコミットが複数存在する場合も正しく処理する
  - _Requirements: 1.1, 1.2, 1.3, 1.4_

## 5. アプリケーションサービスの拡張

- [x] 5.1 CollectCommitsサービスに増分収集の自動判定機能を追加する
  - `sinceDate`が`null`の場合、`CommitCollectionHistoryRepository::findById()`から最新日時を取得する
  - 最新日時が取得できた場合、その日時を`sinceDate`として使用する
  - 最新日時が取得できなかった場合（初回収集など）、`sinceDate`を`null`のままにして全コミットを収集する
  - エラーが発生した場合、フォールバック動作として全コミットを収集する
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 4.1, 4.2_

- [x] 5.2 CollectCommitsサービスに収集履歴の記録機能を追加する
  - コミット収集完了後、収集したコミットの最新日時を取得する
  - `CommitCollectionHistory`エンティティを作成または更新する
  - `CommitCollectionHistoryRepository::save()`を使用して履歴を保存する
  - コミット保存と履歴更新を同一トランザクションで実行する
  - トランザクション失敗時はロールバックする
  - _Requirements: 3.5, 4.4, 5.1, 5.2, 5.3_

- [x] 5.3 CollectCommitsサービスのエラーハンドリングを拡張する
  - 最新コミット日時の取得エラーを適切に処理する
  - データベースエラー時にトランザクションをロールバックする
  - 既存のエラーハンドリングパターンに従う
  - _Requirements: 4.3, 4.4_

## 6. プレゼンテーション層の既存コードリネーム

- [x] 6.1 CommitControllerの`index()`メソッドを`collectShow()`にリネームする
  - メソッド名を変更する
  - ルート定義を更新する（`routes/web.php`）
  - テストファイルを更新する（`CommitControllerTest.php`）
  - リダイレクト先は変更不要（ルート名が同じなら）
  - _Requirements: -_

- [x] 6.2 IndexResponseをCollectShowResponseにリネームする
  - クラス名を変更する
  - ファイル名を変更する
  - 名前空間を更新する
  - 使用箇所を更新する（`CommitController::collectShow()`）
  - _Requirements: -_

## 7. プレゼンテーション層の新規コンポーネント作成

- [x] 7.1 (P) RecollectShowRequestを作成する
  - `BaseRequest`を継承する
  - `rules()`メソッドでバリデーションルールを定義する（現時点では空配列）
  - 既存の`ListRequest`パターンに従う
  - _Requirements: 7.1_

- [x] 7.2 (P) RecollectResponseを作成する
  - `CommitCollectionHistory`のコレクションと`Project`のコレクションを受け取る
  - プロジェクトIDをキーとしたマップを作成する
  - 履歴を配列に変換し、プロジェクト名を追加する
  - プロジェクトが存在しない場合は`Unknown`を表示する
  - `toArray()`メソッドでInertia.js用の配列を返す
  - 既存の`IndexResponse`パターンに従う
  - _Requirements: 7.2, 7.3_

- [x] 7.3 (P) RecollectRequestを作成する
  - `BaseRequest`を継承する
  - `project_id`と`branch_name`のバリデーションルールを定義する
  - `getProjectId()`と`getBranchName()`メソッドを実装する
  - 既存の`CollectRequest`パターンに従う
  - _Requirements: 8.1_

## 8. プレゼンテーション層のコントローラー拡張

- [x] 8.1 CommitControllerに`recollectShow()`メソッドを追加する
  - `CommitCollectionHistoryRepository::findAll()`で収集履歴を取得する
  - `ProjectRepository::findAll()`でプロジェクト情報を取得する
  - `RecollectResponse`を使用してデータを準備する
  - `RecollectShowRequest`でリクエストを処理する（将来の拡張用）
  - `Inertia::render()`で`Commit/Recollect`ページを表示する
  - エラーハンドリングを実装する
  - _Requirements: 7.1, 7.2, 7.3_

- [x] 8.2 CommitControllerに`recollect()`メソッドを追加する
  - `RecollectRequest`でバリデーションを実行する
  - `ProjectId`と`BranchName`の値オブジェクトを作成する
  - `CollectCommits::execute()`を呼び出して再収集を実行する（`sinceDate`は`null`を渡す）
  - 成功時は成功メッセージと共に再収集ページにリダイレクトする
  - エラー時はエラーメッセージと共に再収集ページにリダイレクトする
  - 既存の`collect()`メソッドのパターンに従う
  - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 8.7_

## 9. ルーティングの更新

- [x] 9.1 再収集ページのルートを追加する
  - `GET /commits/recollect`ルートを追加する（`CommitController::recollectShow()`）
  - `POST /commits/recollect`ルートを追加する（`CommitController::recollect()`）
  - ルート名を適切に設定する
  - _Requirements: 7.1, 8.1_

## 10. フロントエンドの実装

- [x] 10.1 (P) RecollectページのTypeScript型定義を追加する
  - `RecollectHistoryItem`インターフェースを定義する
  - `RecollectPageProps`インターフェースを定義する
  - `resources/js/types/commit.d.ts`に追加する
  - _Requirements: 7.2, 7.3_

- [x] 10.2 (P) Recollectページコンポーネントを作成する
  - `resources/js/pages/Commit/Recollect.tsx`を作成する
  - `PageLayout`コンポーネントを使用してページレイアウトを構築する
  - `FlashMessage`コンポーネントでエラー/成功メッセージを表示する
  - `Table`コンポーネントを使用してリストを表示する
  - テーブルヘッダー（プロジェクト名、ブランチ名、前回の最新日時、操作）を定義する
  - 各リストアイテムにプロジェクト名、ブランチ名、前回の最新日時、再収集ボタンを表示する
  - 日時が存在しない場合は「未収集」を表示する
  - 空状態（収集履歴が存在しない場合）を表示する
  - 既存の`Project/Index.tsx`のパターンに従う
  - _Requirements: 7.4, 7.5, 7.6_

- [x] 10.3 Recollectページの再収集ボタン機能を実装する
  - 各「再収集」ボタンに`LoadingButton`コンポーネントを使用する
  - クリック時に`POST /commits/recollect`に`project_id`と`branch_name`を送信する
  - 処理中は`loading`状態を表示する（「再収集中...」）
  - 処理完了後、成功/エラーメッセージを表示してページをリロードする
  - Inertia.jsの`useForm`または`router.post()`を使用する
  - _Requirements: 8.1_

## 11. サービスプロバイダーの更新

- [x] 11.1 CommitCollectionHistoryRepositoryのバインディングを追加する
  - `AppServiceProvider`または適切なサービスプロバイダーでバインディングを設定する
  - `CommitCollectionHistoryRepository`ポートを`EloquentCommitCollectionHistoryRepository`実装にバインドする
  - 既存のリポジトリバインディングのパターンに従う
  - _Requirements: -_

## 12. テストの実装

**注意**: インターフェース（Ports）、型定義、サービスプロバイダーのバインディングについては、TDDの例外としてテスト作成タスクを生成しません。実装クラスのテストで検証されます。

- [x] 12.1 (P) CommitRepositoryの`findLatestCommittedDate()`メソッドのテストを作成する
  - 最新コミット日時を取得できることを確認する
  - コミットが存在しない場合は`null`を返すことを確認する
  - 複数のコミットが同じ日時を持つ場合も正しく処理することを確認する
  - インデックスを使用して効率的にクエリを実行することを確認する
  - _Requirements: 1.1, 1.2, 1.3, 1.4_

- [x] 12.2 (P) CommitCollectionHistoryRepositoryのテストを作成する
  - `save()`メソッドのテスト（新規作成と更新）
  - `findById()`メソッドのテスト
  - `findAll()`メソッドのテスト
  - 複合ユニーク制約のテスト
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 6.1, 6.2, 6.3_

- [x] 12.3 CollectCommitsサービスの増分収集機能のテストを作成する
  - `sinceDate`が`null`の場合、自動判定が実行されることを確認する
  - 最新日時が取得できた場合、その日時以降のコミットのみを収集することを確認する
  - 最新日時が取得できなかった場合、全コミットを収集することを確認する
  - エラーが発生した場合、フォールバック動作が実行されることを確認する
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 4.1, 4.2_

- [x] 12.4 CollectCommitsサービスの収集履歴記録機能のテストを作成する
  - コミット収集完了後、収集履歴が記録されることを確認する
  - 初回収集の場合、新しいレコードが作成されることを確認する
  - 既存のレコードがある場合、最新日時が更新されることを確認する
  - トランザクション内で実行されることを確認する
  - _Requirements: 5.1, 5.2, 5.3_

- [x] 12.5 CommitControllerの`recollectShow()`メソッドのテストを作成する
  - 再収集ページが正しく表示されることを確認する
  - 収集履歴が正しく取得されることを確認する
  - プロジェクト情報が正しく結合されることを確認する
  - エラーハンドリングが正しく動作することを確認する
  - _Requirements: 7.1, 7.2, 7.3_

- [x] 12.6 CommitControllerの`recollect()`メソッドのテストを作成する
  - 再収集が正しく実行されることを確認する
  - バリデーションエラーが正しく処理されることを確認する
  - 成功時にリダイレクトと成功メッセージが表示されることを確認する
  - エラー時にリダイレクトとエラーメッセージが表示されることを確認する
  - _Requirements: 8.1, 8.6, 8.7_

- [x] 12.7 既存のCommitControllerテストを更新する
  - `index()`メソッドのテストを`collectShow()`に更新する
  - メソッド名の変更に対応する
  - _Requirements: -_

- [x] 12.8 統合テストを作成する
  - 増分収集のエンドツーエンドテスト
  - 再収集ページの表示と実行の統合テスト
  - トランザクションの整合性を確認するテスト
  - _Requirements: 2.1, 2.2, 2.3, 3.1, 3.2, 3.3, 3.4, 3.5, 5.1, 5.2, 5.3, 7.1, 7.2, 7.3, 8.1, 8.2, 8.3, 8.4, 8.5_

## 13. ドキュメント更新と最終確認

- [x] 13.1 README.mdを更新する
  - 既存のREADME.mdの構造に従う
  - _Requirements: -_

- [x] 13.3 最終確認として`npm run pre-push`を実行する
  - コード品質チェック（lint、型チェック等）を実施する
  - すべてのチェックが通過することを確認する
  - 問題がある場合は修正してから再実行する
  - _Requirements: -_
