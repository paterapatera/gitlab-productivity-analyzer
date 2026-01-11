# Research & Design Decisions

---
**Purpose**: Capture discovery findings, architectural investigations, and rationale that inform the technical design.

**Usage**:
- Log research activities and outcomes during the discovery phase.
- Document design decision trade-offs that are too detailed for `design.md`.
- Provide references and evidence for future audits or reuse.
---

## Summary
- **Feature**: `gitlab-commit-user-monthly-aggregation`
- **Discovery Scope**: Extension (既存システムの拡張)
- **Key Findings**:
  - Recharts 3.0がReact 19とTypeScript 5.xを完全サポート
  - 既存のクリーンアーキテクチャパターンに従って新規コンポーネントを作成
  - `CollectCommits`サービスの完了後に集計処理を自動実行する統合パターン

## Research Log

### グラフライブラリの選定
- **Context**: 要件5で棒グラフと表によるデータ可視化が必要。React 19、TypeScript 5.7、Tailwind CSS 4との統合性を確認する必要がある。
- **Sources Consulted**: 
  - Recharts 3.0 migration guide (GitHub)
  - Chart.js vs Recharts comparison articles
  - React 19 compatibility reports
- **Findings**: 
  - Recharts 3.0はReact 19とTypeScript 5.xを完全サポート
  - RechartsはReact専用で、TypeScript定義が組み込まれている
  - Chart.jsは`react-chartjs-2`ラッパーが必要で、より複雑なセットアップが必要
  - Rechartsは積み上げ棒グラフと集合縦棒グラフの両方をサポート
- **Implications**: Recharts 3.0を採用。バンドルサイズとパフォーマンスは許容範囲内。Tailwind CSSとの統合は可能。

### 既存アーキテクチャパターンの分析
- **Context**: 既存のコミット収集機能を拡張して集計機能を追加する必要がある。
- **Sources Consulted**: 
  - 既存コードベース（`CollectCommits`サービス、リポジトリパターン）
  - ギャップ分析ドキュメント
- **Findings**: 
  - クリーンアーキテクチャの4層構造が確立されている
  - `BaseService`パターンでトランザクション管理が統一されている
  - リポジトリパターン（Port/Adapter）が一貫して使用されている
  - `ConvertsBetweenEntityAndModel`トレイトでエンティティとEloquentモデルの変換が標準化されている
- **Implications**: 新規コンポーネントも既存パターンに従って実装。`AggregateCommits`サービスは`BaseService`を継承し、集計リポジトリはPortインターフェースを定義。

### タイムゾーン処理
- **Context**: 要件1.6でタイムゾーンを考慮した年月判定が必要。
- **Sources Consulted**: 
  - Laravel 12のタイムゾーン設定
  - PHP DateTimeのタイムゾーン処理
- **Findings**: 
  - Laravelは`config/app.php`の`timezone`設定を使用
  - `Carbon`（Laravelの日時ライブラリ）はタイムゾーンを考慮した操作を提供
  - `committed_date`から年月を抽出する際、アプリケーションのタイムゾーン設定を考慮する必要がある
- **Implications**: `Carbon`を使用してタイムゾーンを考慮した年月抽出を実装。集計サービス内で`Carbon::parse($committedDate)->setTimezone(config('app.timezone'))`を使用。

### 集計範囲の判定ロジック
- **Context**: 要件2.8-2.10で最終集計月から先月までのデータを集計し、既存集計月をスキップする必要がある。
- **Sources Consulted**: 
  - 既存の`CommitCollectionHistory`の実装
  - データベースクエリパターン
- **Findings**: 
  - `CommitCollectionHistory`は最終収集日時を記録しているが、集計月の記録は別途必要
  - 集計リポジトリに`findLatestAggregationMonth()`メソッドを追加して最終集計月を取得
  - 先月までのデータのみを集計するため、現在日時から先月の最終日を計算
- **Implications**: 集計リポジトリに最終集計月を取得するメソッドを追加。集計サービスで集計範囲を判定するロジックを実装。

## Architecture Pattern Evaluation

| Option | Description | Strengths | Risks / Limitations | Notes |
|--------|-------------|-----------|---------------------|-------|
| クリーンアーキテクチャ（既存） | 4層構造（Domain, Application, Infrastructure, Presentation） | 既存パターンと一貫性、テスタビリティ、保守性 | 新規コンポーネントの追加が必要 | 既存システムと整合性を保つため採用 |
| 既存サービスへの統合 | `CollectCommits`サービスに集計処理を追加 | 最小限の変更 | 単一責任原則違反、テスト困難 | 推奨しない |

## Design Decisions

### Decision: 新規コンポーネントの作成（Option B）
- **Context**: 集計機能を既存のコミット収集機能に統合する方法を検討。
- **Alternatives Considered**:
  1. Option A: 既存コンポーネントの拡張 — `CollectCommits`サービスに集計処理を追加
  2. Option B: 新規コンポーネントの作成 — 独立した`AggregateCommits`サービスを作成
  3. Option C: ハイブリッドアプローチ — 新規サービスを作成し、`CollectCommits`から呼び出し
- **Selected Approach**: Option B（新規コンポーネントの作成）
- **Rationale**: 
  - 責任の明確な分離（集計機能は収集機能とは独立）
  - テスト容易性（集計ロジックを独立してテスト可能）
  - 既存コンポーネントの複雑度を抑制
  - 将来的な集計機能の拡張が容易
- **Trade-offs**: 
  - メリット: 保守性向上、テスト容易性、拡張性
  - デメリット: ファイル数の増加、インターフェース設計の注意が必要
- **Follow-up**: 集計サービスのインターフェース設計と`CollectCommits`への統合方法を設計ドキュメントで詳細化

### Decision: Recharts 3.0の採用
- **Context**: グラフ表示機能に必要なライブラリの選定。
- **Alternatives Considered**:
  1. Recharts 3.0 — React専用、TypeScript定義組み込み
  2. Chart.js + react-chartjs-2 — 汎用的、ラッパー必要
  3. Victory — React専用、より複雑な機能
- **Selected Approach**: Recharts 3.0
- **Rationale**: 
  - React 19とTypeScript 5.xを完全サポート
  - TypeScript定義が組み込まれており、追加の型定義パッケージが不要
  - 積み上げ棒グラフと集合縦棒グラフの両方をサポート
  - React専用で、Reactのライフサイクルと統合しやすい
- **Trade-offs**: 
  - メリット: セットアップが簡単、React統合が良好、型安全性
  - デメリット: バンドルサイズがやや大きい（許容範囲内）
- **Follow-up**: 実装時にパフォーマンステストを実施し、必要に応じて最適化

### Decision: 集計テーブルの設計
- **Context**: 月次集計データを永続化するためのテーブル設計。
- **Alternatives Considered**:
  1. 複合プライマリキー: `(project_id, branch_name, author_email, year, month)`
  2. 単一プライマリキー + ユニーク制約
- **Selected Approach**: 複合プライマリキー
- **Rationale**: 
  - 要件1.3で同一キーの集計データは更新（重複を作成しない）と明示
  - 複合プライマリキーにより、データベースレベルで一意性を保証
  - クエリパフォーマンスの向上（プライマリキーがインデックスとして機能）
- **Trade-offs**: 
  - メリット: データ整合性、クエリパフォーマンス
  - デメリット: キーサイズが大きい（許容範囲内）
- **Follow-up**: インデックス設計を最適化し、大量データ時のパフォーマンスを検証

## Risks & Mitigations
- **リスク1**: グラフライブラリのパフォーマンス問題 — **緩和策**: 実装時にパフォーマンステストを実施し、必要に応じてデータ量を制限または仮想化を検討
- **リスク2**: 大量データの集計処理のパフォーマンス — **緩和策**: データベースインデックスを最適化し、必要に応じてバッチ処理を検討
- **リスク3**: タイムゾーン処理の正確性 — **緩和策**: タイムゾーン設定を明確に文書化し、テストで検証

## References
- [Recharts 3.0 Migration Guide](https://github.com/recharts/recharts/wiki/3.0-migration-guide) — React 19互換性の確認
- [Laravel 12 Documentation](https://laravel.com/docs/12.x) — タイムゾーン設定とCarbonの使用方法
- 既存コードベース — `CollectCommits`サービス、リポジトリパターンの参考
