# Implementation Plan

- 1. ドメイン層の実装
- [x] 1.1 (P) バリューオブジェクトの実装
  - ProjectId、ProjectDescription、ProjectNameWithNamespace、DefaultBranchの各バリューオブジェクトクラスを作成
  - 各バリューオブジェクトで不変性を保証（readonlyプロパティ）
  - コンストラクタで値の検証を実装（空文字列チェック、型チェックなど）
  - 等価性の比較メソッドを実装
  - _Requirements: 2.4_

- [x] 1.2 (P) Projectエンティティの実装
  - Projectエンティティクラスを作成（イミュータブル、readonlyプロパティ）
  - 必須フィールドの検証ロジックを実装（プロジェクトID、名前空間付きプロジェクト名）
  - バリューオブジェクトを使用してプロパティを定義
  - ドメイン層はフレームワーク非依存であることを確認
  - _Requirements: 2.4_

- 2. データベースマイグレーションの実装
- [x] 2.1 プロジェクトテーブルのマイグレーション作成
  - projectsテーブルのマイグレーションファイルを作成
  - カラム定義: id（BIGINT UNSIGNED、プライマリキー）、description（TEXT、nullable）、name_with_namespace（VARCHAR(500)、NOT NULL）、default_branch（VARCHAR(255)、nullable）、deleted_at（TIMESTAMP、nullable）
  - deleted_atカラムにインデックスを追加（ソフトデリート用）
  - テーブルエンジンと文字セットを設定（InnoDB、utf8mb4）
  - _Requirements: 2.1, 2.2_

- 3. インフラストラクチャ層の実装（GitLab API統合）
- [x] 3.1 (P) FetchesProjectsトレイトの実装
  - GitLab APIからプロジェクト一覧を取得する機能を提供するトレイトを作成
  - Laravel HTTP Clientを使用してAPIリクエストを送信
  - PRIVATE-TOKENヘッダーで認証を実装
  - ページネーション処理を実装（offset-based方式、全ページを取得）
  - APIレスポンスをProjectエンティティに変換するロジックを実装
  - エラーハンドリング（認証エラー、タイムアウト、APIエラー）を実装
  - 429エラー時の指数バックオフとリトライを実装
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6_

- [x] 3.2 GitLabApiClientの実装
  - GetProjectsFromGitLabUseCaseインターフェースを実装するクラスを作成
  - FetchesProjectsトレイトを使用してプロジェクト取得機能を実装
  - 認証トークンの管理機能を実装（環境変数から取得）
  - 共通のエラーハンドリングを実装
  - execute()メソッドで全プロジェクトを取得して返却
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6_

- 4. アプリケーション層の実装（インターフェース定義）
- [x] 4.1 (P) GetProjectsFromGitLabUseCaseインターフェースの定義
  - GitLab APIからプロジェクト一覧を取得するインターフェースを定義
  - execute()メソッドのシグネチャを定義（戻り値: array<Project>、例外: GitLabApiException）
  - アプリケーション層とインフラストラクチャ層の境界を定義
  - _Requirements: 1.1, 1.2, 1.6_

- [x] 4.2 (P) ProjectRepositoryインターフェースの定義
  - プロジェクトデータアクセスのインターフェースを定義
  - findAll()、findByProjectId()、save()、saveMany()、delete()、findNotInProjectIds()メソッドを定義
  - アプリケーション層とインフラストラクチャ層の境界を定義
  - _Requirements: 2.1, 2.2_

- 5. インフラストラクチャ層の実装（データアクセス）
- [x] 5.1 EloquentProjectRepositoryの実装
  - ProjectRepositoryインターフェースを実装するEloquent実装を作成
  - Eloquentモデルを作成（ProjectEloquentModel）
  - ProjectエンティティとEloquentモデルの相互変換を実装
  - バリューオブジェクトとデータベースカラムの相互変換を実装
  - findAll()、findByProjectId()、save()、saveMany()、delete()、findNotInProjectIds()メソッドを実装
  - エラーハンドリングを実装
  - _Requirements: 2.1, 2.2, 2.3_

- 6. アプリケーション層の実装（ユースケース）
- [x] 6.1 PersistProjectsUseCaseの実装
  - プロジェクト情報をデータベースに永続化するユースケースを実装
  - ProjectRepositoryを使用してデータアクセス
  - 既存プロジェクトは更新、新規プロジェクトは作成するロジックを実装
  - トランザクション内で処理を実行
  - エンティティの必須フィールド検証を実行
  - エラーハンドリングを実装
  - _Requirements: 2.1, 2.2, 2.4, 2.5_

- [x] 6.2 SyncProjectsUseCaseの実装
  - プロジェクト情報をGitLab APIから取得して同期するユースケースを実装
  - GetProjectsFromGitLabUseCaseを使用してプロジェクト取得
  - PersistProjectsUseCaseを使用して永続化
  - ProjectRepositoryを使用して削除されたプロジェクトを検出
  - 削除されたプロジェクトにdeleted_atタイムスタンプを設定（ソフトデリート）
  - エラー時は部分的な更新を許可し、エラーを記録
  - SyncResultオブジェクトを返却（同期結果の情報を含む）
  - _Requirements: 4.1, 4.2, 4.3, 4.4_

- 7. プレゼンテーション層の実装
- [x] 7.1 (P) Request/Responseクラスの実装
  - Project/ListRequest.php、Project/ListResponse.phpを作成
  - リクエストとレスポンスの変換ロジックを実装
  - バリデーションルールを定義（必要に応じて）
  - _Requirements: 3.1, 4.1_

- [x] 7.2 ProjectControllerの実装
  - プロジェクト関連のHTTPリクエストを処理するコントローラーを作成
  - index()メソッド: プロジェクト一覧を取得してInertia.jsページを返却（ProjectRepositoryを使用）
  - sync()メソッド: 同期リクエストを処理（SyncProjectsUseCaseを使用）、リダイレクト
  - エラーハンドリングを実装（適切なHTTPステータスコードとエラーメッセージ）
  - Inertia.jsレスポンスの生成
  - _Requirements: 3.1, 4.1_

- 8. フロントエンドの実装
- [x] 8.1 ProjectPageコンポーネントの実装
  - プロジェクト一覧を表示するInertia.jsページコンポーネントを作成
  - プロジェクト一覧の表示（テーブルまたはカード形式）
  - 同期ボタンの実装（Inertia.jsのuseFormを使用）
  - ローディング状態の表示（同期処理中）
  - エラー状態の表示（エラーメッセージ）
  - 空状態の表示（プロジェクトが存在しない場合）
  - TypeScript型定義を実装
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 4.1_

- 9. 統合と設定
- [x] 9.1 ルーティングの設定
  - /projects（GET）: プロジェクト一覧表示
  - /projects/sync（POST）: プロジェクト同期
  - routes/web.phpにルートを追加
  - _Requirements: 3.1, 4.1_

- [x] 9.2 サービスプロバイダーの設定
  - GetProjectsFromGitLabUseCaseインターフェースとGitLabApiClient実装のバインディング
  - ProjectRepositoryインターフェースとEloquentProjectRepository実装のバインディング
  - AppServiceProviderまたは専用のServiceProviderで設定
  - _Requirements: 1.1, 2.1_

- [x] 9.3 環境変数の設定
  - GitLab APIのベースURLと認証トークンの環境変数を定義
  - .env.exampleに設定例を追加
  - 設定ファイル（config/services.phpなど）に設定を追加
  - _Requirements: 1.1_

- 10. テストの実装
- [x] 10.1 (P) ドメイン層のユニットテスト
  - Projectエンティティの必須フィールド検証テスト
  - バリューオブジェクトの検証ロジックテスト
  - イミュータビリティのテスト
  - _Requirements: 2.4_

- [x] 10.2 (P) インフラストラクチャ層のユニットテスト
  - FetchesProjectsトレイトのページネーション処理テスト
  - FetchesProjectsトレイトのエンティティ変換テスト
  - GitLabApiClientのインターフェース実装テスト
  - EloquentProjectRepositoryのデータアクセステスト
  - モックを使用したテスト
  - _Requirements: 1.1, 1.2, 1.6, 2.1, 2.2_

- [x] 10.3 (P) アプリケーション層のユニットテスト
  - PersistProjectsUseCaseの保存・更新ロジックテスト
  - SyncProjectsUseCaseの同期ロジックテスト
  - モックを使用したテスト
  - _Requirements: 2.1, 2.2, 4.1, 4.2, 4.3, 4.4_

- [x] 10.4 統合テスト
  - GitLab API統合テスト（モック使用）
  - データベース統合テスト（プロジェクトの保存・更新・削除）
  - 同期フローの統合テスト
  - _Requirements: 1.1, 1.2, 2.1, 2.2, 4.1, 4.2_

- [x] 10.5 E2E/UIテスト
  - プロジェクト一覧表示のテスト
  - 同期ボタンクリックのテスト
  - ローディング状態のテスト
  - エラー状態のテスト
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 4.1_

- 11. Storybookの実装
- [x] 11.1 ProjectPageコンポーネントのStorybookストーリー作成
  - ProjectPage.stories.tsxを作成
  - 正常状態のストーリー（プロジェクト一覧が表示される状態）
  - 空状態のストーリー（プロジェクトが存在しない状態）
  - ローディング状態のストーリー（同期処理中）
  - エラー状態のストーリー（エラーが発生した状態）
  - Inertia.jsのモックを設定
  - _Requirements: 3.1, 3.2, 3.3, 3.4_

- 12. ドキュメントの更新
- [x] 12.1 README.mdの作成
  - プロジェクトの概要とセットアップ手順を記載
  - GitLab API設定方法を記載
  - 環境変数の設定方法を記載
  - 開発コマンドの説明を追加

- [x] 12.2 AGENTS.mdの確認と更新
  - 現在の内容を確認
  - 必要に応じてStorybookに関する記述を追加または更新
  - フロントエンド開発時のStorybook使用に関するガイドラインを確認
