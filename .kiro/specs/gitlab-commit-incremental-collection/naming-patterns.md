# 命名パターン一覧表

## 現在の状態（既存コード + 設計ドキュメント）

| 機能 | HTTP Method | エンドポイント | コントローラーメソッド | Requestクラス | Responseクラス | 用途 |
|------|-------------|---------------|---------------------|--------------|---------------|------|
| **収集** | GET | `/commits/collect` | `index()` | なし | `IndexResponse` | フォーム表示 |
| **収集** | POST | `/commits/collect` | `collect()` | `CollectRequest` | なし（リダイレクト） | 収集実行 |
| **再収集** | GET | `/commits/recollect` | `recollectIndex()` | `RecollectIndexRequest` | `RecollectResponse` | 一覧表示 |
| **再収集** | POST | `/commits/recollect` | `recollect()` | `RecollectRequest` | なし（リダイレクト） | 再収集実行 |

## 提案1: 意味的な正確性を優先（推奨）

| 機能 | HTTP Method | エンドポイント | コントローラーメソッド | Requestクラス | Responseクラス | 用途 |
|------|-------------|---------------|---------------------|--------------|---------------|------|
| **収集** | GET | `/commits/collect` | `collectShow()` | `CollectShowRequest`（新規） | `CollectShowResponse`（`IndexResponse`をリネーム） | フォーム表示 |
| **収集** | POST | `/commits/collect` | `collect()` | `CollectRequest` | なし（リダイレクト） | 収集実行 |
| **再収集** | GET | `/commits/recollect` | `recollectIndex()` | `RecollectIndexRequest` | `RecollectResponse` | 一覧表示 |
| **再収集** | POST | `/commits/recollect` | `recollect()` | `RecollectRequest` | なし（リダイレクト） | 再収集実行 |

**変更点**:
- `index()` → `collectShow()`
- `IndexResponse` → `CollectShowResponse`
- `CollectShowRequest` を新規作成（既存コードではRequestクラスを使用していないが、一貫性のため追加）

## 提案2: アクションベースの命名パターンで完全に統一

| 機能 | HTTP Method | エンドポイント | コントローラーメソッド | Requestクラス | Responseクラス | 用途 |
|------|-------------|---------------|---------------------|--------------|---------------|------|
| **収集** | GET | `/commits/collect` | `collectShow()` | `CollectShowRequest`（新規） | `CollectShowResponse`（`IndexResponse`をリネーム） | フォーム表示 |
| **収集** | POST | `/commits/collect` | `collect()` | `CollectRequest` | なし（リダイレクト） | 収集実行 |
| **再収集** | GET | `/commits/recollect` | `recollectShow()` | `RecollectShowRequest`（`RecollectIndexRequest`をリネーム） | `RecollectResponse` | 一覧表示 |
| **再収集** | POST | `/commits/recollect` | `recollect()` | `RecollectRequest` | なし（リダイレクト） | 再収集実行 |

**変更点**:
- `index()` → `collectShow()`
- `IndexResponse` → `CollectShowResponse`
- `CollectShowRequest` を新規作成
- `recollectIndex()` → `recollectShow()`
- `RecollectIndexRequest` → `RecollectShowRequest`

## 提案3: 最小限の変更（既存コードのRequestクラスは追加しない）

| 機能 | HTTP Method | エンドポイント | コントローラーメソッド | Requestクラス | Responseクラス | 用途 |
|------|-------------|---------------|---------------------|--------------|---------------|------|
| **収集** | GET | `/commits/collect` | `collectShow()` | なし | `CollectShowResponse`（`IndexResponse`をリネーム） | フォーム表示 |
| **収集** | POST | `/commits/collect` | `collect()` | `CollectRequest` | なし（リダイレクト） | 収集実行 |
| **再収集** | GET | `/commits/recollect` | `recollectShow()` | `RecollectShowRequest`（`RecollectIndexRequest`をリネーム） | `RecollectResponse` | 一覧表示 |
| **再収集** | POST | `/commits/recollect` | `recollect()` | `RecollectRequest` | なし（リダイレクト） | 再収集実行 |

**変更点**:
- `index()` → `collectShow()`
- `IndexResponse` → `CollectShowResponse`
- `recollectIndex()` → `recollectShow()`
- `RecollectIndexRequest` → `RecollectShowRequest`
- 収集機能のGETメソッドではRequestクラスを使用しない（既存コードのパターンを維持）

## 比較表

| 項目 | 提案1 | 提案2 | 提案3 |
|------|-------|-------|-------|
| **命名パターンの統一性** | 部分的（`Show`と`Index`が混在） | 完全（すべて`Show`） | 完全（すべて`Show`） |
| **意味的な正確性** | 高い（`Show`はフォーム、`Index`は一覧） | 中程度（`Show`が一覧表示にも使われる） | 中程度（`Show`が一覧表示にも使われる） |
| **既存コードへの影響** | 中程度（Requestクラス追加） | 中程度（Requestクラス追加） | 最小限（Requestクラス追加なし） |
| **Laravel標準パターンとの一致** | 高い | 中程度 | 中程度 |
