# Research & Design Decisions

---
**Purpose**: Capture discovery findings, architectural investigations, and rationale that inform the technical design.

**Usage**:
- Log research activities and outcomes during the discovery phase.
- Document design decision trade-offs that are too detailed for `design.md`.
- Provide references and evidence for future audits or reuse.
---

## Summary
- **Feature**: `gitlab-user-monthly-aggregation`
- **Discovery Scope**: Extension (既存システムの拡張)
- **Key Findings**:
  - `components.json`が存在し、shadcn/uiの設定が完了している。shadcn/ui CLIを使用してCheckboxコンポーネントを追加可能
  - Inertia.jsでは`router.get()`で配列をクエリパラメータとして送信可能（Laravel側で配列として受信可能）
  - 既存の`AggregationShowResponse`の集計ロジックパターンを参考にできるが、ユーザーキーの生成方法が異なる（既存: `project_id-branch_name-author_email`、新機能: `author_email`のみ）

## Research Log

### shadcn/ui Checkbox実装パターン
- **Context**: チェックボックスUIコンポーネントの実装方法を確認
- **Sources Consulted**: 
  - Web検索結果: shadcn/uiとRadix UIの違い、shadcn/uiの使用方法
  - 既存のプロジェクト設定: `components.json`が存在し、shadcn/uiの設定が完了している
  - 既存のUIコンポーネント: `resources/js/components/ui/select.tsx`, `resources/js/components/ui/button.tsx`
  - package.json: `shadcn` CLIがdevDependenciesに含まれ、`@radix-ui/react-checkbox` v1.1.4が既にインストール済み
- **Findings**: 
  - `components.json`が存在し、shadcn/uiの設定が完了している
  - 既存のUIコンポーネント（`select.tsx`, `button.tsx`）はshadcn/uiのパターンに従っている（Radix UIプリミティブをラップし、Tailwind CSSでスタイリング）
  - shadcn/uiはRadix UIをベースにしたスタイル済みコンポーネントを提供し、コピー&ペースト可能なコードとしてプロジェクトに追加される
  - `npx shadcn@latest add checkbox`コマンドでCheckboxコンポーネントを追加可能
  - 生成されたコンポーネントは既存のデザインシステム（Tailwind CSS、`cn()`関数）に自動統合される
- **Implications**: 
  - shadcn/ui CLIを使用してCheckboxコンポーネントを追加
  - 既存のUIコンポーネントパターンと一貫性を保つ
  - 必要に応じて、生成されたコンポーネントをカスタマイズ可能

### Inertia.jsでの複数選択値のクエリパラメータ送信
- **Context**: チェックボックスで選択された複数のユーザーをクエリパラメータとして送信する方法を確認
- **Sources Consulted**: 
  - Web検索結果: Inertia.jsでの配列クエリパラメータの扱い
  - 既存の実装: `resources/js/pages/Commit/Aggregation.tsx`でのフィルタリング実装
- **Findings**: 
  - Inertia.jsの`router.get()`メソッドで配列を直接渡すことが可能
  - Laravel側では`$request->query('users', [])`で配列として受信可能
  - `preserveState: true`と`preserveScroll: true`を使用してページ遷移なしで更新可能（既存のAggregation.tsxで使用中）
- **Implications**: 
  - チェックボックスで選択されたユーザーの配列を`router.get()`で送信
  - Laravel側のリクエストクラスで配列バリデーションを実装（`'users' => ['nullable', 'array']`, `'users.*' => ['string', 'email']`）

### 既存の集計ロジックパターン
- **Context**: 既存の`AggregationShowResponse`の集計ロジックを参考に、ユーザー単位での集計ロジックを設計
- **Sources Consulted**: 
  - 既存の実装: `app/Presentation/Response/Commit/AggregationShowResponse.php`
  - ギャップ分析: `.kiro/specs/gitlab-user-monthly-aggregation/gap-analysis.md`
- **Findings**: 
  - 既存の`AggregationShowResponse`は`buildChartData()`と`buildTableData()`メソッドで集計データを変換
  - ユーザーキーの生成方法が異なる（既存: `sprintf('%d-%s-%s', $project_id, $branch_name, $author_email)`、新機能: `author_email`のみ）
  - 既存のロジックは月ごと、ユーザーごとにグループ化してデータを構築
- **Implications**: 
  - `UserProductivityShowResponse`に類似の集計ロジックを実装
  - ユーザーキーは`author_email`のみを使用（複数リポジトリにまたがる同一ユーザーを統合）
  - 月ごとに`total_additions`、`total_deletions`、`commit_count`を合計

## Architecture Pattern Evaluation

| Option | Description | Strengths | Risks / Limitations | Notes |
|--------|-------------|-----------|---------------------|-------|
| Hybrid Approach | リポジトリ層は拡張、プレゼンテーション層は新規作成 | 既存機能への影響を最小化、責任の分離 | 実装計画がやや複雑 | ギャップ分析で推奨されたアプローチ |
| Extend Existing | 既存のコントローラーとリポジトリを拡張 | 新規ファイル作成が最小限 | コントローラーとリポジトリが複雑化 | 単一責任の原則に反する可能性 |
| Create New | すべてのコンポーネントを新規作成 | 責任の分離が明確 | 新規ファイルが増える | 既存パターンとの一貫性を保つ必要がある |

## Design Decisions

### Decision: リポジトリメソッドの設計
- **Context**: プロジェクト・ブランチを指定せず、全ユーザーの集計データを取得する必要がある
- **Alternatives Considered**:
  1. 既存の`findByProjectAndBranch`メソッドを拡張してプロジェクト・ブランチをオプションにする
  2. 新規メソッド`findByUsersAndYear`を作成する
- **Selected Approach**: 新規メソッド`findByUsersAndYear(array $authorEmails, ?int $year)`を作成
- **Rationale**: 
  - 既存の`findByProjectAndBranch`はプロジェクト・ブランチ単位の取得に特化している
  - 新機能は異なるクエリパターンを使用するため、別メソッドとして分離することで責任が明確になる
  - 既存機能への影響を最小化できる
- **Trade-offs**: 
  - ✅ 責任の分離が明確
  - ✅ 既存機能への影響なし
  - ❌ リポジトリインターフェースが拡張される
- **Follow-up**: 実装時にクエリパフォーマンスを確認

### Decision: 集計ロジックの配置
- **Context**: 複数リポジトリにまたがる同一ユーザーのデータを統合するロジックの配置場所
- **Alternatives Considered**:
  1. アプリケーションサービス層に配置（`GetUserProductivity`サービス）
  2. レスポンスクラスに配置（`UserProductivityShowResponse`）
- **Selected Approach**: `UserProductivityShowResponse`に集計ロジックを配置
- **Rationale**: 
  - 既存の`AggregationShowResponse`と同様のパターンに従う
  - 集計ロジックは表示用データの変換に近く、レスポンスクラスに配置するのが適切
  - アプリケーションサービス層は不要（単純なデータ取得と変換のみ）
- **Trade-offs**: 
  - ✅ 既存パターンとの一貫性
  - ✅ シンプルな実装
  - ⚠️ レスポンスクラスがやや複雑になる可能性
- **Follow-up**: 集計ロジックの複雑度を監視し、必要に応じてサービス層に分離を検討

### Decision: UIコンポーネントの実装
- **Context**: チェックボックスUIコンポーネントの実装方法
- **Alternatives Considered**:
  1. Radix UIプリミティブを直接使用して手動でラッパーコンポーネントを作成
  2. shadcn/ui CLIを使用してCheckboxコンポーネントを追加
- **Selected Approach**: shadcn/ui CLIを使用してCheckboxコンポーネントを追加
- **Rationale**: 
  - `components.json`が存在し、shadcn/uiの設定が完了している
  - 既存のUIコンポーネント（`select.tsx`, `button.tsx`）はshadcn/uiのパターンに従っている
  - shadcn/ui CLIを使用することで、既存のデザインシステムに自動統合され、一貫性が保たれる
  - 生成されたコンポーネントはカスタマイズ可能で、プロジェクトのコードとして所有できる
- **Trade-offs**: 
  - ✅ 既存パターンとの一貫性
  - ✅ デザインシステムへの自動統合
  - ✅ 開発効率の向上（CLIで即座に追加可能）
  - ✅ カスタマイズ可能（生成されたコードを直接編集可能）
- **Follow-up**: shadcn/ui CLIでコンポーネントを追加後、必要に応じてカスタマイズ

### Decision: ルーティング
- **Context**: ユーザー生産性画面のルート設計
- **Alternatives Considered**:
  1. 既存の`/commits/aggregation`ルートを拡張
  2. 新規ルート`/commits/user-productivity`を作成
- **Selected Approach**: 新規ルート`/commits/user-productivity`を作成
- **Rationale**: 
  - 既存の集計機能とは異なる責任を持つ（プロジェクト・ブランチ単位 vs ユーザー単位）
  - ルートを分離することで、機能の独立性を保つ
  - 既存の`CommitController`とは別の`UserProductivityController`を作成することで責任を分離
- **Trade-offs**: 
  - ✅ 責任の分離が明確
  - ✅ 既存機能への影響なし
  - ❌ 新規ルートとコントローラーが必要
- **Follow-up**: ルート名とコントローラー名の一貫性を確認

## Risks & Mitigations
- **チェックボックスUIコンポーネントの実装**: shadcn/ui CLIを使用することで、既存のデザインシステムに自動統合され、一貫性が保たれる。必要に応じて生成されたコンポーネントをカスタマイズ可能
- **複数リポジトリにまたがる集計ロジックの正確性**: 既存の`AggregationShowResponse`のロジックを参考にし、ユニットテストで検証することでリスクを軽減
- **クエリパフォーマンス**: 大量のデータがある場合のパフォーマンスを考慮し、必要に応じてインデックスを追加

## References
- [shadcn/ui Documentation](https://ui.shadcn.com/) — UIコンポーネントライブラリ
- [shadcn/ui Checkbox Component](https://ui.shadcn.com/docs/components/checkbox) — チェックボックスコンポーネントの追加方法
- [Radix UI Checkbox Documentation](https://www.radix-ui.com/primitives/docs/components/checkbox) — チェックボックスプリミティブの参考（shadcn/uiが使用）
- [Inertia.js Forms Documentation](https://inertiajs.com/forms) — フォームとクエリパラメータの扱い
- 既存の実装: `app/Presentation/Response/Commit/AggregationShowResponse.php` — 集計ロジックの参考
- 既存の実装: `resources/js/components/ui/select.tsx` — UIコンポーネントパターンの参考
- 既存の設定: `components.json` — shadcn/uiの設定ファイル
