# Implementation Plan

## TDD実装時の注意事項

**テスト作成の例外**: TDDの原則に従って実装を進めますが、以下の場合はテスト作成タスクを生成しません：

- **インターフェース（Ports）**: 型定義のみで実装がないため、実装クラスのテストで検証されます
  - 例: `CommitCollectionHistoryRepository`インターフェースのテストは不要、`EloquentCommitCollectionHistoryRepository`実装クラスのテストで検証
- **型定義**: 型システムが既に契約を強制しているため、リフレクションによるシグネチャ検証テストは不要
- **サービスプロバイダーのバインディング**: バインディング設定は動作テストで自然に検証されるため、専用のバインディングテストは不要

**テストの焦点**: テストは**動作と機能**を対象とし、**実装詳細**は対象外です。インターフェースや型定義の変更は、実装クラスのテストが失敗することで自然に検出されます。

詳細は`.kiro/settings/rules/tasks-generation.md`の「6. Test-Driven Development (TDD) Guidelines」を参照してください。

## Tasks

- [ ] 1. 依存関係の追加
- [x] 1.1 `@radix-ui/react-popover` パッケージをインストールする
  - `vendor/bin/sail npm install @radix-ui/react-popover` を実行
  - `package.json` に依存関係が追加されることを確認
  - _Requirements: 4.1_

- [ ] 2. Combobox コンポーネントの実装
- [x] 2.1 `resources/js/components/ui/combobox.tsx` を新規作成する
  - Radix UI の `Popover` プリミティブをインポート
  - 既存の `Input` コンポーネントをインポート
  - `CommitProject` 型をインポート（`resources/js/types/commit.d.ts`）
  - `ComboboxProps` インターフェースを定義（設計ドキュメントの `Service Interface` セクションを参照）
  - _Requirements: 1.1-1.4, 2.1-2.6, 3.1-3.3, 4.1-4.7_

- [x] 2.2 Combobox コンポーネントの基本構造を実装する
  - `Popover.Root`, `Popover.Trigger`, `Popover.Content` を使用した基本構造
  - `useState` で `open` と `searchValue` の内部状態を管理
  - 制御コンポーネントとして `value` と `onValueChange` プロップを受け取る
  - `Popover.Trigger` に既存の `SelectTrigger` と同様のスタイリングを適用
  - `data-slot` 属性を使用したスタイリング
  - _Requirements: 1.1, 3.1-3.3, 4.1, 4.6_

- [x] 2.3 検索入力フィールドを実装する
  - `Popover.Content` 内に `Input` コンポーネントを配置
  - `searchValue` state と連携
  - プレースホルダーテキスト「プロジェクトを検索...」を設定
  - 検索アイコン（`lucide-react` の `SearchIcon`）を表示
  - 既存の `Input` コンポーネントのスタイリングを維持
  - _Requirements: 1.2-1.4, 4.7_

- [x] 2.4 フィルタリングロジックを実装する
  - `useMemo` を使用してフィルタリング結果を計算
  - `projects` と `searchValue` に基づいてフィルタリング
  - 大文字小文字を区別しない部分一致（`toLowerCase()` と `includes()` を使用）
  - 空文字列の場合はすべてのプロジェクトを表示
  - _Requirements: 2.1-2.3, 2.5-2.6_

- [x] 2.5 プロジェクト一覧の表示を実装する
  - フィルタリング結果をスクロール可能なリストとして表示
  - 各プロジェクトをクリック可能なアイテムとして表示
  - プロジェクト選択時に `onValueChange` を呼び出し、`open` を `false` に設定
  - 選択されたプロジェクト名を `Popover.Trigger` に表示
  - 最大高さを設定し、スクロール可能にする（`max-h-[300px]` など）
  - _Requirements: 2.1-2.3, 3.1-3.3, 4.2-4.3_

- [x] 2.6 空状態の表示を実装する
  - フィルタリング結果が空の場合、「該当するプロジェクトが見つかりません」メッセージを表示
  - 空状態のスタイリングを実装（既存のデザインシステムに従う）
  - _Requirements: 2.4_

- [x] 2.7 Popover の開閉制御を実装する
  - 外側クリックで閉じる（Radix UI が自動的に処理）
  - Escapeキーで閉じる（Radix UI が自動的に処理）
  - プロジェクト選択時に自動的に閉じる
  - _Requirements: 3.3, 4.4-4.5_

- [x] 2.8 スタイリングを既存の Select コンポーネントと一貫性を保つ
  - `SelectTrigger` と同様のスタイリングを `Popover.Trigger` に適用
  - `SelectContent` と同様のスタイリングを `Popover.Content` に適用
  - Tailwind CSS クラスを使用
  - `data-slot` 属性を使用
  - _Requirements: 4.6_

- [ ] 3. Commit/Index.tsx での統合
- [x] 3.1 `Select` コンポーネントを `Combobox` に置き換える
  - `Combobox` コンポーネントをインポート
  - `Select` コンポーネントのインポートを削除（使用箇所のみ）
  - `Select` を `Combobox` に置き換え
  - `value` と `onValueChange` プロップを設定（既存の `projectId` state と連携）
  - `projects` プロップを渡す
  - `placeholder` プロップを設定（「プロジェクトを選択してください」）
  - `aria-invalid` プロップを設定（`errors.project_id` に基づく）
  - `required` プロップを設定
  - `id` プロップを設定（`project_id`）
  - _Requirements: 3.1-3.6_

- [x] 3.2 既存のバリデーション機能を確認する
  - `errors.project_id` の表示が正常に動作することを確認
  - フォーム送信時のバリデーションが正常に動作することを確認
  - _Requirements: 3.5-3.6_

- [ ] 4. テストの実装
- [ ] 4.1 `Combobox` コンポーネントのユニットテストを作成する
  - `resources/js/test/components/ui/combobox.test.tsx` を新規作成
  - フィルタリングロジックのテスト
    - 大文字小文字を区別しない部分一致のテスト
    - 空文字列の場合のテスト
    - 一致するプロジェクトがない場合のテスト
  - プロジェクト選択処理のテスト
    - プロジェクト選択時の `onValueChange` 呼び出しのテスト
    - Popover の開閉制御のテスト
  - _Requirements: 2.1-2.6, 3.1-3.3_

- [ ] 4.2 `Commit/Index.tsx` での統合テストを作成する
  - `tests/Feature/Presentation/Controller/CommitControllerTest.php` を確認し、必要に応じて更新
  - プロジェクト選択とフォーム送信のテスト
  - バリデーションエラーの表示テスト
  - _Requirements: 3.4-3.6_

- [ ] 4.3 E2E/UI テストを作成する（オプション）
  - コミット収集画面でのプロジェクト検索機能のテスト
  - 検索入力とフィルタリングのテスト
  - プロジェクト選択とフォーム送信のテスト
  - Escapeキーでの閉じる操作のテスト
  - _Requirements: 1.1-1.4, 2.1-2.6, 3.1-3.3, 4.1-4.7_

- [ ] 5. ドキュメント更新と最終確認
- [ ] 5.1 README.mdを更新する
  - 既存のREADME.mdの構造に従う

- [ ] 5.2 最終確認として`npm run pre-push`を実行する
  - コード品質チェック（lint、型チェック等）を実施する
  - すべてのチェックが通過することを確認する
  - 問題がある場合は修正してから再実行する
