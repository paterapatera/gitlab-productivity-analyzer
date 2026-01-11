# Requirements Document

## Project Description (Input)
GitLabのコミットの追加行と削除行をユーザーごとの1ヶ月毎に集計して永続化したい。
Aggrigation.tsxと既存の「集計」と似た機能だが、違うところは1ユーザーに対して複数リポジトリのコミットが対象になること。
CommitUserMonthlyAggregationの取得結果を計算して、集計結果を出力すること。
新しくテーブルを作ったりはしない。
機能名は「ユーザー生産性」とする。

## Introduction
本機能は、GitLabのコミットデータをユーザー単位で月次集計し、複数リポジトリにまたがる生産性を可視化する「ユーザー生産性」機能を提供します。既存の`CommitUserMonthlyAggregation`テーブルのデータを使用し、1ユーザーに対して複数リポジトリのコミットを集計して表示します。既存の「集計」機能と同様に、グラフと表でデータを可視化しますが、集計単位がプロジェクト・ブランチ単位からユーザー単位に変更されます。

## Requirements

### Requirement 1: ユーザー月次集計データの取得
**Objective:** As a ユーザー, I want 複数リポジトリにまたがるユーザーごとの月次集計データを取得できる, so that ユーザーの生産性を横断的に分析できる

#### Acceptance Criteria
1. When ユーザー生産性画面にアクセスした時, the User Productivity Service shall `CommitUserMonthlyAggregation`テーブルから全ユーザーの月次集計データを取得する
2. When 年フィルターが指定された時, the User Productivity Service shall 指定された年の集計データのみを取得する
3. When ユーザー（author_email）フィルターが複数指定された時, the User Productivity Service shall 指定されたユーザーの集計データのみを取得する
4. The User Productivity Service shall 複数リポジトリにまたがる同一ユーザーの集計データを統合して返却する

### Requirement 2: ユーザー単位での集計計算
**Objective:** As a ユーザー, I want 複数リポジトリのコミットデータをユーザー単位で集計できる, so that ユーザーの総合的な生産性を把握できる

#### Acceptance Criteria
1. When `CommitUserMonthlyAggregation`から集計データを取得した時, the User Productivity Service shall 同一ユーザー（author_email）のデータを月ごとに統合する
2. When 同一ユーザーの複数リポジトリのデータを統合する時, the User Productivity Service shall `total_additions`を合計する
3. When 同一ユーザーの複数リポジトリのデータを統合する時, the User Productivity Service shall `total_deletions`を合計する
4. When 同一ユーザーの複数リポジトリのデータを統合する時, the User Productivity Service shall `commit_count`を合計する
5. When 集計データが存在しない時, the User Productivity Service shall 空のコレクションを返却する

### Requirement 3: グラフ表示機能
**Objective:** As a ユーザー, I want ユーザーごとの月次生産性をグラフで可視化できる, so that 生産性の推移を視覚的に把握できる

#### Acceptance Criteria
1. When 集計データが存在する時, the User Productivity Service shall 月別の追加行数と削除行数を棒グラフで表示する
2. When グラフデータを構築する時, the User Productivity Service shall ユーザーごとに色分けされたスタックバーを表示する
3. When グラフデータを構築する時, the User Productivity Service shall X軸に月（1月〜12月）を表示する
4. When グラフデータを構築する時, the User Productivity Service shall Y軸に行数を表示する
5. When グラフデータを構築する時, the User Productivity Service shall 凡例にユーザー名を表示する
6. When 集計データが存在しない時, the User Productivity Service shall グラフを表示せず、空状態メッセージを表示する

### Requirement 4: 表表示機能
**Objective:** As a ユーザー, I want ユーザーごとの月次生産性を表で確認できる, so that 詳細な数値を確認できる

#### Acceptance Criteria
1. When 集計データが存在する時, the User Productivity Service shall ユーザー名と月ごとの合計行数（追加行数+削除行数）を表で表示する
2. When 表データを構築する時, the User Productivity Service shall 行にユーザー名を表示する
3. When 表データを構築する時, the User Productivity Service shall 列に月（1月〜12月）を表示する
4. When 表データを構築する時, the User Productivity Service shall セルに月ごとの合計行数を表示する
5. When 該当する月のデータが存在しない時, the User Productivity Service shall セルに0を表示する
6. When 集計データが存在しない時, the User Productivity Service shall 表を表示せず、空状態メッセージを表示する

### Requirement 5: フィルタリング機能
**Objective:** As a ユーザー, I want 年やユーザーで集計データをフィルタリングできる, so that 特定期間や特定ユーザーの生産性を分析できる

#### Acceptance Criteria
1. When 年フィルターが変更された時, the User Productivity Service shall 指定された年の集計データのみを表示する
2. When ユーザーフィルター（チェックボックス）が変更された時, the User Productivity Service shall 選択されたユーザーの集計データのみを表示する
3. When 複数のユーザーが選択された時, the User Productivity Service shall 選択された全ユーザーの集計データを表示する
4. When フィルターが変更された時, the User Productivity Service shall ページ遷移を行わず、クエリパラメータを更新する
5. When フィルターが変更された時, the User Productivity Service shall グラフと表を更新する
6. When 年フィルターが未選択の時, the User Productivity Service shall 全年の集計データを表示する
7. When ユーザーフィルターが未選択の時, the User Productivity Service shall 全ユーザーの集計データを表示する

### Requirement 6: 年一覧の取得
**Objective:** As a ユーザー, I want 利用可能な年を選択できる, so that データが存在する期間のみを分析できる

#### Acceptance Criteria
1. When ユーザー生産性画面にアクセスした時, the User Productivity Service shall `CommitUserMonthlyAggregation`テーブルから利用可能な年を抽出する
2. When 年一覧を取得する時, the User Productivity Service shall 重複を除去して昇順でソートする
3. When 集計データが存在しない時, the User Productivity Service shall 空の年一覧を返却する

### Requirement 7: ユーザー一覧の取得と選択機能
**Objective:** As a ユーザー, I want 利用可能なユーザーを確認し、複数選択してフィルタリングできる, so that 特定ユーザーの生産性を分析できる

#### Acceptance Criteria
1. When ユーザー生産性画面にアクセスした時, the User Productivity Service shall `CommitUserMonthlyAggregation`テーブルから利用可能なユーザー（author_email）を抽出する
2. When ユーザー一覧を取得する時, the User Productivity Service shall 重複を除去してユーザー名（author_name）でソートする
3. When ユーザー名が存在しない時, the User Productivity Service shall "Unknown"を表示する
4. When ユーザー一覧を表示する時, the User Productivity Service shall チェックボックスで各ユーザーを選択可能にする
5. When 複数のユーザーを選択した時, the User Productivity Service shall 選択されたユーザーの集計データのみを表示する
6. When 全ユーザーのチェックボックスを選択した時, the User Productivity Service shall 全ユーザーの集計データを表示する
7. When ユーザーのチェックボックスを解除した時, the User Productivity Service shall そのユーザーの集計データを非表示にする

### Requirement 8: データ永続化の利用
**Objective:** As a システム, I want 既存の`CommitUserMonthlyAggregation`テーブルを使用する, so that 新規テーブルを作成せずにデータを利用できる

#### Acceptance Criteria
1. The User Productivity Service shall 新規テーブルを作成しない
2. The User Productivity Service shall 既存の`CommitUserMonthlyAggregation`テーブルからデータを取得する
3. The User Productivity Service shall 既存の`CommitUserMonthlyAggregationRepository`を使用してデータにアクセスする

