# Research & Design Decisions

---
**Purpose**: Capture discovery findings, architectural investigations, and rationale that inform the technical design.

**Usage**:
- Log research activities and outcomes during the discovery phase.
- Document design decision trade-offs that are too detailed for `design.md`.
- Provide references and evidence for future audits or reuse.
---

## Summary
- **Feature**: `project-filter`
- **Discovery Scope**: Extension (既存システムの拡張)
- **Key Findings**:
  - Radix UI の `Popover` を使用した Combobox パターンが実装可能
  - 既存の `Select` コンポーネントのパターンに従い、新しい `Combobox` コンポーネントを作成
  - フィルタリングロジックはクライアント側で実装（既存の `projects` プロップを活用）
  - `@radix-ui/react-popover` の追加インストールが必要

## Research Log

### Radix UI Popover の使用方法
- **Context**: 検索可能なセレクトコンポーネントを実装するために、Radix UI の Popover コンポーネントの使用方法を調査
- **Sources Consulted**: 
  - Web検索結果: Radix UI Popover の公式ドキュメントと使用例
  - Web検索結果: Radix UI を使用した Combobox パターンの実装例
- **Findings**: 
  - `@radix-ui/react-popover` は `Popover.Root`, `Popover.Trigger`, `Popover.Content` などのコンポーネントを提供
  - `Popover.Content` は `sideOffset` プロップで位置調整が可能
  - `Popover.Portal` を使用してポータルレンダリングが可能
  - `open` と `onOpenChange` プロップで開閉状態を制御
  - Escapeキーでの閉じる操作は Radix UI が自動的に処理
- **Implications**: 
  - `@radix-ui/react-popover` を追加インストールする必要がある
  - 既存の `Select` コンポーネントと同様のパターンで実装可能
  - キーボード操作（Escapeキー）は Radix UI が自動的に処理するため、追加実装は不要

### 検索可能なセレクトの実装パターン
- **Context**: Radix UI を使用した検索可能なセレクトコンポーネントのベストプラクティスを調査
- **Sources Consulted**: 
  - Web検索結果: Radix UI Popover と Command コンポーネントを組み合わせた Combobox パターン
  - 既存のコードベース: `Select` コンポーネントの実装パターン
- **Findings**: 
  - `Popover` と `Input` を組み合わせて Combobox を実装するパターンが一般的
  - `Command` コンポーネント（`@radix-ui/react-command`）は存在しないが、`cmdk` パッケージが一般的に使用される
  - ただし、シンプルなフィルタリングの場合は `Input` と手動フィルタリングで十分
  - 既存の `Input` コンポーネントを再利用可能
- **Implications**: 
  - `Command` コンポーネントは不要。`Input` と手動フィルタリングで実装
  - 既存の `Input` コンポーネントのパターンに従う
  - フィルタリングロジックは `Combobox` コンポーネント内で実装

### 既存のデザインシステムとの統合
- **Context**: 既存の `Select` コンポーネントと一貫性を保つためのスタイリング方法を調査
- **Sources Consulted**: 
  - 既存のコードベース: `resources/js/components/ui/select.tsx`
  - 既存のコードベース: `resources/js/components/ui/input.tsx`
- **Findings**: 
  - 既存のコンポーネントは `data-slot` 属性を使用してスタイリング
  - Tailwind CSS クラスによるスタイリング
  - `cn()` ユーティリティ関数を使用したクラス名のマージ
  - `SelectTrigger` と同様のスタイリングを `ComboboxTrigger` に適用可能
  - `SelectContent` と同様のスタイリングを `ComboboxContent` に適用可能
- **Implications**: 
  - `Combobox` コンポーネントは既存の `Select` コンポーネントと同様のスタイリングパターンに従う
  - `data-slot` 属性を使用してスタイリング
  - Tailwind CSS クラスによる一貫性のあるデザイン

### パフォーマンス最適化
- **Context**: 大量のプロジェクト（100件以上）がある場合のフィルタリングパフォーマンスを検討
- **Sources Consulted**: 
  - 既存のコードベース: `resources/js/pages/Commit/Index.tsx` でのプロジェクト一覧の扱い
- **Findings**: 
  - プロジェクト一覧は既にクライアント側に渡されている（`projects` プロップ）
  - フィルタリングは単純な文字列マッチング（`includes()` メソッド）
  - 100件程度のプロジェクトであれば、パフォーマンス問題は発生しない
  - 仮想スクロールは現時点では不要
- **Implications**: 
  - フィルタリングロジックは `Combobox` コンポーネント内で実装
  - パフォーマンス最適化は現時点では不要
  - 将来的に大量のプロジェクト（500件以上）がある場合は、仮想スクロールの検討が必要

## Architecture Pattern Evaluation

| Option | Description | Strengths | Risks / Limitations | Notes |
|--------|-------------|-----------|---------------------|-------|
| 新しい Combobox コンポーネント | Radix UI Popover と Input を組み合わせた新しいコンポーネント | 関心の分離、再利用性、既存コードへの影響が最小限 | 新しいコンポーネントファイルが必要、Popover の追加インストールが必要 | 既存の Select コンポーネントのパターンに従う |

## Design Decisions

### Decision: Combobox コンポーネントの実装アプローチ
- **Context**: 検索可能なセレクトコンポーネントを実装するためのアプローチを決定
- **Alternatives Considered**:
  1. 既存の `Select` コンポーネントを拡張 — Radix UI の制約により実装が困難
  2. 新しい `Combobox` コンポーネントを作成 — 関心の分離と再利用性が高い
- **Selected Approach**: 新しい `Combobox` コンポーネントを作成
- **Rationale**: 
  - 既存の `Select` コンポーネントに影響を与えない
  - 関心の分離を保ちながら、再利用可能なコンポーネントとして実装可能
  - 既存のデザインシステム（Tailwind CSS、Radix UI）のパターンに従える
- **Trade-offs**: 
  - ✅ 既存コードへの影響が最小限
  - ✅ 再利用性が高い
  - ❌ 新しいコンポーネントファイルが必要
  - ❌ `@radix-ui/react-popover` の追加インストールが必要
- **Follow-up**: 
  - `Combobox` コンポーネントの実装とテスト
  - 既存の `Select` コンポーネントとの一貫性確認

### Decision: フィルタリングロジックの実装場所
- **Context**: フィルタリングロジックをどこに実装するかを決定
- **Alternatives Considered**:
  1. `Combobox` コンポーネント内で実装 — シンプルで直接的な実装
  2. カスタムフックに分離 — 再利用性が高いが、現時点では過剰
- **Selected Approach**: `Combobox` コンポーネント内で実装
- **Rationale**: 
  - フィルタリングロジックは `Combobox` コンポーネント専用の機能
  - 現時点では他の場所で再利用する予定がない
  - シンプルで直接的な実装が可能
- **Trade-offs**: 
  - ✅ シンプルで直接的な実装
  - ✅ コンポーネント内で完結
  - ❌ 将来的に他の場所で再利用する場合は、カスタムフックへの分離が必要
- **Follow-up**: 
  - 将来的に他の場所で再利用する場合は、カスタムフックへの分離を検討

### Decision: 既存のデザインシステムとの統合方法
- **Context**: 既存の `Select` コンポーネントと一貫性を保つためのスタイリング方法を決定
- **Alternatives Considered**:
  1. 既存の `Select` コンポーネントのスタイリングをそのまま使用 — 一貫性が高い
  2. 新しいスタイリングを作成 — 柔軟性が高いが、一貫性が低下
- **Selected Approach**: 既存の `Select` コンポーネントのスタイリングパターンに従う
- **Rationale**: 
  - 既存のデザインシステムとの一貫性を保つ
  - `data-slot` 属性と Tailwind CSS クラスによるスタイリングパターンに従う
  - ユーザー体験の一貫性を保つ
- **Trade-offs**: 
  - ✅ 既存のデザインシステムとの一貫性
  - ✅ ユーザー体験の一貫性
  - ❌ カスタマイズの柔軟性がやや制限される
- **Follow-up**: 
  - `Combobox` コンポーネントのスタイリング実装
  - 既存の `Select` コンポーネントとの視覚的一貫性確認

## Risks & Mitigations
- **Radix UI Popover の追加インストール** — 既知のパターンで、ドキュメントが充実しているため、リスクは低い
- **既存の Select コンポーネントへの影響** — 新しいコンポーネントを作成するため、既存のコンポーネントへの影響はない
- **パフォーマンス問題** — 100件程度のプロジェクトであれば、パフォーマンス問題は発生しない。将来的に大量のプロジェクトがある場合は、仮想スクロールの検討が必要

## References
- [Radix UI Popover Documentation](https://www.radix-ui.com/primitives/docs/components/popover) — Radix UI Popover の公式ドキュメント
- [Shadcn Combobox Documentation](https://www.shadcn.io/ui/combobox) — Shadcn の Combobox 実装例（参考）
