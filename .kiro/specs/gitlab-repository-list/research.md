# Research & Design Decisions

---
**Purpose**: Capture discovery findings, architectural investigations, and rationale that inform the technical design.

**Usage**:
- Log research activities and outcomes during the discovery phase.
- Document design decision trade-offs that are too detailed for `design.md`.
- Provide references and evidence for future audits or reuse.
---

## Summary
- **Feature**: `gitlab-repository-list`
- **Discovery Scope**: New Feature
- **Key Findings**:
  - GitLab API v4のプロジェクト一覧エンドポイントは包括的なフィールドセットを返却し、offset-basedとkeyset-basedの2つのページネーション方式をサポート
  - Laravelでのクリーンアーキテクチャ実装は、リポジトリインターフェースをアプリケーション層に配置し、インフラストラクチャ層で実装するパターンが標準
  - GitLab APIのレート制限は認証済みリクエストではユーザー単位、未認証ではIP単位で適用され、指数バックオフ戦略が推奨される

## Research Log

### GitLab API v4 プロジェクト一覧エンドポイントの詳細仕様

- **Context**: リポジトリ情報を取得するためのGitLab APIエンドポイントの詳細仕様を調査
- **Sources Consulted**: 
  - [GitLab Projects API documentation](https://docs.gitlab.com/api/projects/)
  - [GitLab REST API documentation](https://docs.gitlab.com/api/rest/)
  - [GitLab Repositories API documentation](https://docs.gitlab.com/ja-jp/api/repositories/#list-repository-tree)
- **Findings**: 
  - エンドポイント: `GET /api/v4/projects`
  - レスポンスには以下の主要フィールドが含まれる: `id`, `name`, `description`, `path`, `path_with_namespace`, `created_at`, `default_branch`, `ssh_url_to_repo`, `http_url_to_repo`, `web_url`, `avatar_url`など
  - 認証は`PRIVATE-TOKEN`ヘッダーまたは`Authorization: Bearer`ヘッダーで行う
  - ページネーションは2つの方式をサポート:
    - Offset-based: `page`と`per_page`パラメータ（デフォルト20、最大100）
    - Keyset-based: `pagination=keyset`、`order_by=id`、`sort=asc|desc`（大規模コレクションに効率的）
  - レスポンスヘッダーに`X-Total`, `X-Total-Pages`, `X-Page`, `X-Per-Page`, `X-Next-Page`, `X-Prev-Page`が含まれる
  - 10,000件を超える場合は`X-Total`と`X-Total-Pages`が返却されない（パフォーマンス理由）
- **Implications**: 
  - リポジトリエンティティにはGitLab APIの主要フィールドをマッピングする必要がある
  - ページネーション処理はoffset-basedを初期実装として採用し、将来的にkeyset-basedへの移行を検討可能にする設計
  - 大量データ処理時は`X-Total`が利用できない可能性を考慮した実装が必要

### GitLab API レート制限と認証

- **Context**: API呼び出し時のレート制限と認証方法のベストプラクティスを調査
- **Sources Consulted**: 
  - [GitLab Rate Limiting documentation](https://docs.gitlab.com/ee/administration/settings/rate_limit_on_users_api.html)
  - [GitLab Handbook - Rate Limiting](https://handbook.gitlab.com/handbook/engineering/infrastructure-platforms/rate-limiting/)
- **Findings**: 
  - レート制限は認証済みリクエストではユーザー単位、未認証ではIP単位で適用
  - エンドポイントごとに異なる制限（例: Users APIは100-240リクエスト/分）
  - レート制限エラーはHTTP 429ステータスコード
  - 指数バックオフ戦略が推奨される
  - セルフマネージドインスタンスでは管理者がカスタムレート制限を設定可能
- **Implications**: 
  - エラーハンドリングに429エラーの処理とリトライロジックを実装
  - レート制限に達した場合のユーザーへの適切なフィードバックが必要
  - 認証トークンは環境変数で管理し、セキュアに保存

### Laravel クリーンアーキテクチャ実装パターン

- **Context**: Laravelでのクリーンアーキテクチャとリポジトリパターンの実装方法を調査
- **Sources Consulted**: 
  - [Laravel Repository Pattern implementation guides](https://github.com/AREXTOVID/laravel-repository-service-pattern)
  - [Clean Architecture in Laravel - DEV Community](https://dev.to/giacomomasseron/clean-architecture-in-a-laravel-project-3oh3)
- **Findings**: 
  - リポジトリインターフェースはアプリケーション層（`/app/Application/`）に配置
  - リポジトリ実装はインフラストラクチャ層（`/app/Infrastructure/`）に配置
  - サービスプロバイダーでインターフェースと実装のバインディングを行う
  - コントローラーは依存性注入でリポジトリインターフェースを受け取る
  - テスト時はモックリポジトリを使用可能
- **Implications**: 
  - アーキテクチャの層構造を明確に分離
  - リポジトリインターフェースをアプリケーション層に定義し、Eloquent実装をインフラストラクチャ層に配置
  - サービスプロバイダーでバインディングを登録

## Architecture Pattern Evaluation

| Option | Description | Strengths | Risks / Limitations | Notes |
|--------|-------------|-----------|---------------------|-------|
| Clean Architecture | 4層構造（Presentation, Application, Domain, Infrastructure） | 明確な責任分離、テスタビリティ、フレームワーク非依存のドメイン層 | 初期実装のファイル数が多い、学習コスト | プロジェクトのsteeringで既に定義済み |
| MVC | Model-View-Controller | シンプル、Laravel標準 | ビジネスロジックとデータアクセスの混在 | プロジェクトの方針と不一致 |

## Design Decisions

### Decision: クリーンアーキテクチャの採用

- **Context**: プロジェクトのsteeringで既にクリーンアーキテクチャが定義されているが、実装は未開始
- **Alternatives Considered**:
  1. MVCパターン — Laravel標準の構造を使用
  2. クリーンアーキテクチャ — 4層構造で明確な分離
- **Selected Approach**: クリーンアーキテクチャを採用し、4層構造（Presentation, Application, Domain, Infrastructure）で実装
- **Rationale**: プロジェクトのsteeringで既に定義されており、将来の拡張性とテスタビリティを考慮
- **Trade-offs**: 
  - メリット: 明確な責任分離、独立したテスト、将来の変更への柔軟性
  - デメリット: 初期実装のファイル数が多い、学習コスト
- **Follow-up**: 実装時に各層の責任範囲を明確にする

### Decision: ページネーション方式の選択

- **Context**: GitLab APIはoffset-basedとkeyset-basedの2つのページネーション方式をサポート
- **Alternatives Considered**:
  1. Offset-based — シンプル、全ページ対応が容易
  2. Keyset-based — 大規模データに効率的
- **Selected Approach**: 初期実装ではoffset-basedを採用
- **Rationale**: 実装のシンプルさと、要件では全ページを取得する必要があるため
- **Trade-offs**: 
  - メリット: 実装がシンプル、全ページ数の取得が可能
  - デメリット: 大規模データではパフォーマンスが劣る可能性
- **Follow-up**: 将来的にkeyset-basedへの移行を検討可能にする設計

### Decision: 手動同期方式の採用

- **Context**: 要件で同期処理は手動で行うことが指定されている
- **Alternatives Considered**:
  1. 自動同期（バッチジョブ/スケジューラー） — 定期的な自動更新
  2. 手動同期（リアルタイム処理） — ユーザーがボタンをクリックして実行
- **Selected Approach**: 手動同期方式を採用し、リアルタイム処理で実装
- **Rationale**: 要件で明示的に手動同期が指定されている
- **Trade-offs**: 
  - メリット: 実装がシンプル、ユーザーが同期タイミングを制御可能
  - デメリット: ユーザーが忘れる可能性、最新性の保証がない
- **Follow-up**: 同期中のUI状態管理（ローディング、進捗表示）を適切に実装

## Risks & Mitigations

- **GitLab APIのレート制限** — 指数バックオフとリトライロジックを実装し、429エラーを適切に処理
- **大量データ処理時のパフォーマンス** — ページネーション処理を最適化し、必要に応じてkeyset-basedへの移行を検討
- **認証トークンの管理** — 環境変数で管理し、セキュリティベストプラクティスに従う
- **同期処理の長時間実行** — タイムアウト設定と進捗表示を実装し、ユーザー体験を向上

## References

- [GitLab Projects API documentation](https://docs.gitlab.com/api/projects/) — プロジェクト一覧エンドポイントの詳細仕様
- [GitLab REST API documentation](https://docs.gitlab.com/api/rest/) — ページネーションと認証の詳細
- [GitLab Repositories API documentation](https://docs.gitlab.com/ja-jp/api/repositories/#list-repository-tree) — リポジトリツリーエンドポイントの詳細仕様（参考）
- [GitLab Rate Limiting documentation](https://docs.gitlab.com/ee/administration/settings/rate_limit_on_users_api.html) — レート制限の詳細
- [Laravel Repository Pattern implementation](https://github.com/AREXTOVID/laravel-repository-service-pattern) — Laravelでのクリーンアーキテクチャ実装例
