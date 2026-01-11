# Requirements Document

## Introduction
この仕様は、GitLabのコミット収集機能において、再収集時に前回収集した最新のコミット以降のコミットのみを収集する増分収集機能を定義します。これにより、初回収集時は全コミットを収集し、2回目以降の収集時は前回の続きから最新までを効率的に収集できるようになります。

## Requirements

### Requirement 1: 最新コミット日時の取得
**Objective:** As a システム, I want 指定されたプロジェクトとブランチの最新コミット日時を取得できる, so that 再収集時に前回の続きから収集できる

#### Acceptance Criteria
1. When コミットリポジトリから最新コミット日時を取得する要求が発生した, the Commit Repository shall 指定されたプロジェクトIDとブランチ名に一致するコミットのうち、最も新しいコミット日時を返す
2. When 指定されたプロジェクトとブランチにコミットが存在しない場合, the Commit Repository shall null を返す
3. When 複数のコミットが同じ日時を持つ場合, the Commit Repository shall その日時を返す
4. The Commit Repository shall コミット日時のインデックスを使用して効率的にクエリを実行する

### Requirement 2: 増分収集の自動判定
**Objective:** As a システム, I want 前回収集した最新コミット日時を自動的に判定して増分収集を実行できる, so that ユーザーが手動で日時を指定する必要がなくなる

#### Acceptance Criteria
1. When コミット収集サービスが実行された, the Collect Commits Service shall 指定されたプロジェクトIDとブランチ名で最新コミット日時を取得する
2. When 最新コミット日時が取得できた場合, the Collect Commits Service shall その日時以降のコミットのみをGitLab APIから取得する
3. When 最新コミット日時が取得できなかった場合（初回収集など）, the Collect Commits Service shall 全コミットを収集する（sinceDate を null として処理）
4. When コミット収集が完了した, the Collect Commits Service shall 収集結果を返す

### Requirement 3: 増分収集の正確性
**Objective:** As a システム, I want 増分収集が正確に前回の続きから収集される, so that コミットの重複や欠落が発生しない

#### Acceptance Criteria
1. When 前回収集した最新コミット日時以降のコミットを収集する場合, the Collect Commits Service shall GitLab APIの `since` パラメータにその日時を設定する
2. When GitLab APIからコミットを取得する場合, the GitLab API Client shall `since` パラメータをUTCタイムゾーンでフォーマットして送信する
3. When 同じ日時のコミットが複数存在する場合, the Collect Commits Service shall その日時以降のコミットも含めて収集する（`since` パラメータは「以降」を意味する）
4. When 既存のコミットが再度収集された場合, the Commit Repository shall 既存のレコードを更新する（重複エラーを発生させない）
5. When 増分収集が実行された, the Collect Commits Service shall 新しく収集されたコミットをデータベースに保存または更新する

### Requirement 4: エラーハンドリング
**Objective:** As a システム, I want 増分収集時のエラーを適切に処理できる, so that システムの安定性と信頼性を確保できる

#### Acceptance Criteria
1. When 最新コミット日時の取得中にエラーが発生した場合, the Collect Commits Service shall エラー結果を返し、全コミットを収集するフォールバック動作を実行する
2. When 最新コミット日時の取得が失敗した場合, the Collect Commits Service shall 初回収集として扱い、全コミットを収集する
3. When 増分収集の実行中にGitLab APIエラーが発生した場合, the Collect Commits Service shall 既存のエラーハンドリングパターンに従ってエラー結果を返す
4. When データベースエラーが発生した場合, the Collect Commits Service shall トランザクションをロールバックし、エラー結果を返す

### Requirement 5: 収集履歴の記録
**Objective:** As a システム, I want プロジェクトとブランチごとの前回収集最新日時を記録できる, so that 増分収集の状態を管理できる

#### Acceptance Criteria
1. When コミット収集が完了した, the Collect Commits Service shall 収集したコミットの最新日時を収集履歴テーブルに記録する
2. When プロジェクトとブランチの組み合わせが初回収集の場合, the Collect Commits Service shall 新しいレコードを作成する
3. When プロジェクトとブランチの組み合わせが既に存在する場合, the Collect Commits Service shall 既存のレコードの最新日時を更新する
4. The Collect Commits History Repository shall プロジェクトID、ブランチ名、最新コミット日時を保存する
5. The Collect Commits History Repository shall プロジェクトIDとブランチ名の組み合わせで一意性を保証する

### Requirement 6: 収集履歴の取得
**Objective:** As a システム, I want プロジェクトとブランチごとの前回収集最新日時を取得できる, so that 増分収集の開始点を決定できる

#### Acceptance Criteria
1. When 指定されたプロジェクトIDとブランチ名で収集履歴を取得する要求が発生した, the Collect Commits History Repository shall 該当するレコードの最新コミット日時を返す
2. When 指定されたプロジェクトIDとブランチ名の収集履歴が存在しない場合, the Collect Commits History Repository shall null を返す
3. When すべてのプロジェクトとブランチの組み合わせの収集履歴を取得する要求が発生した, the Collect Commits History Repository shall すべてのレコードを返す

### Requirement 7: 再収集ページの表示
**Objective:** As a ユーザー, I want プロジェクトとブランチごとの収集履歴を一覧表示できる, so that 再収集を実行する前に状態を確認できる

#### Acceptance Criteria
1. When 再収集ページにアクセスした, the Commit Controller shall すべてのプロジェクトとブランチの組み合わせの収集履歴を取得する
2. When 再収集ページを表示する場合, the Commit Controller shall プロジェクト名、ブランチ名、前回の最新コミット日時を含むデータをInertia.jsページに渡す
3. When 収集履歴が存在しないプロジェクトとブランチの組み合わせがある場合, the Commit Controller shall 前回の最新コミット日時を null として表示する
4. The Recollection Page shall プロジェクト名とブランチ名の組み合わせごとにリストアイテムを表示する
5. The Recollection Page shall 各リストアイテムに前回の最新コミット日時を表示する
6. The Recollection Page shall 各リストアイテムに「再収集」ボタンを表示する

### Requirement 8: 再収集の実行
**Objective:** As a ユーザー, I want 指定されたプロジェクトとブランチのコミットを再収集できる, so that 前回の続きから最新までを効率的に収集できる

#### Acceptance Criteria
1. When 再収集ボタンがクリックされた, the Commit Controller shall 指定されたプロジェクトIDとブランチ名で再収集を実行する
2. When 再収集が実行された, the Collect Commits Service shall 前回収集した最新コミット日時以降のコミットを収集する
3. When 前回収集した最新コミット日時が存在する場合, the Collect Commits Service shall その日時以降のコミットのみをGitLab APIから取得する
4. When 前回収集した最新コミット日時が存在しない場合, the Collect Commits Service shall 全コミットを収集する
5. When 再収集が完了した, the Collect Commits Service shall 収集結果を返し、収集履歴を更新する
6. When 再収集が完了した, the Commit Controller shall 成功メッセージと共に再収集ページにリダイレクトする
7. When 再収集中にエラーが発生した場合, the Commit Controller shall エラーメッセージと共に再収集ページにリダイレクトする

