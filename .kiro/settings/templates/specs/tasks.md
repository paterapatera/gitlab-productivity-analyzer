# Implementation Plan

## TDD実装時の注意事項

**テスト作成の例外**: TDDの原則に従って実装を進めますが、以下の場合はテスト作成タスクを生成しません：

- **インターフェース（Ports）**: 型定義のみで実装がないため、実装クラスのテストで検証されます
  - 例: `CommitCollectionHistoryRepository`インターフェースのテストは不要、`EloquentCommitCollectionHistoryRepository`実装クラスのテストで検証
- **型定義**: 型システムが既に契約を強制しているため、リフレクションによるシグネチャ検証テストは不要
- **サービスプロバイダーのバインディング**: バインディング設定は動作テストで自然に検証されるため、専用のバインディングテストは不要

**テストの焦点**: テストは**動作と機能**を対象とし、**実装詳細**は対象外です。インターフェースや型定義の変更は、実装クラスのテストが失敗することで自然に検出されます。

詳細は`.kiro/settings/rules/tasks-generation.md`の「6. Test-Driven Development (TDD) Guidelines」を参照してください。

## Task Format Template

Use whichever pattern fits the work breakdown:

### Major task only
- [ ] {{NUMBER}}. {{TASK_DESCRIPTION}}{{PARALLEL_MARK}}
  - {{DETAIL_ITEM_1}} *(Include details only when needed. If the task stands alone, omit bullet items.)*
  - _Requirements: {{REQUIREMENT_IDS}}_

### Major + Sub-task structure
- [ ] {{MAJOR_NUMBER}}. {{MAJOR_TASK_SUMMARY}}
- [ ] {{MAJOR_NUMBER}}.{{SUB_NUMBER}} {{SUB_TASK_DESCRIPTION}}{{SUB_PARALLEL_MARK}}
  - {{DETAIL_ITEM_1}}
  - {{DETAIL_ITEM_2}}
  - _Requirements: {{REQUIREMENT_IDS}}_ *(IDs only; do not add descriptions or parentheses.)*

### Final Documentation & Validation Section (Required)
Always include as the final major task group:
- [ ] {{FINAL_NUMBER}}. ドキュメント更新と最終確認
- [ ] {{FINAL_NUMBER}}.1 README.mdを更新する
  - 既存のREADME.mdの構造に従う

- [ ] {{FINAL_NUMBER}}.2 最終確認として`npm run pre-push`を実行する
  - コード品質チェック（lint、型チェック等）を実施する
  - すべてのチェックが通過することを確認する
  - 問題がある場合は修正してから再実行する

> **Parallel marker**: Append ` (P)` only to tasks that can be executed in parallel. Omit the marker when running in `--sequential` mode.
>
> **Optional test coverage**: When a sub-task is deferrable test work tied to acceptance criteria, mark the checkbox as `- [ ]*` and explain the referenced requirements in the detail bullets.
