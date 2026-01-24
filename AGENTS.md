# AI-DLC and Spec-Driven Development

Kiro-style Spec Driven Development implementation on AI-DLC (AI Development Life Cycle)

## Project Context

### Paths

- Steering: `.kiro/steering/`
- Specs: `.kiro/specs/`

### Steering vs Specification

**Steering** (`.kiro/steering/`) - Guide AI with project-wide rules and context
**Specs** (`.kiro/specs/`) - Formalize development process for individual features

### Active Specifications

- Check `.kiro/specs/` for active specifications
- Use `/kiro/spec-status [feature-name]` to check progress

## Development Guidelines

- Think in English, generate responses in Japanese. All Markdown content written to project files (e.g., requirements.md, design.md, tasks.md, research.md, validation reports) MUST be written in the target language configured for this specification (see spec.json.language).
- Before doing any UI, frontend or React development, ALWAYS call the storybook MCP server and shadcn MCP to get further instructions.
- Before doing any backend development or Inertia development, ALWAYS call the laravel-boost MCP server to get further instructions.

## Minimal Workflow

- Phase 0 (optional): `/kiro/steering`, `/kiro/steering-custom`
- Phase 1 (Specification):
    - `/kiro/spec-init "description"`
    - `/kiro/spec-requirements {feature}`
    - `/kiro/validate-gap {feature}` (optional: for existing codebase)
    - `/kiro/spec-design {feature} [-y]`
    - `/kiro/validate-design {feature}` (optional: design review)
    - `/kiro/spec-tasks {feature} [-y]`
- Phase 2 (Implementation): `/kiro/spec-impl {feature} [tasks]`
    - `/kiro/validate-impl {feature}` (optional: after implementation)
- Progress check: `/kiro/spec-status {feature}` (use anytime)

## Development Rules

- 3-phase approval workflow: Requirements → Design → Tasks → Implementation
- Human review required each phase; use `-y` only for intentional fast-track
- Keep steering current and verify alignment with `/kiro/spec-status`
- Follow the user's instructions precisely, and within that scope act autonomously: gather the necessary context and complete the requested work end-to-end in this run, asking questions only when essential information is missing or the instructions are critically ambiguous.

## Steering Configuration

- Load entire `.kiro/steering/` as project memory
- Default files: `product.md`, `tech.md`, `structure.md`
- Custom files are supported (managed via `/kiro/steering-custom`)

# Coding Guidelines

## Backend-First Principle

**基本方針**: バックエンドでできる処理はバックエンドで行い、フロントエンドでは行わない。

### 原則

- **データ処理**: ソート、フィルタリング、集計、変換などのデータ処理はバックエンドで実行
- **バリデーション**: データの検証とバリデーションはバックエンドで実行
- **計算ロジック**: 複雑な計算やビジネスロジックはバックエンドで実行
- **データ整形**: グラフ用データ、表用データなどのデータ整形はバックエンドで実行

### フロントエンドの役割

- **表示**: バックエンドから受け取ったデータを表示する
- **ユーザーインタラクション**: ユーザーの操作を受け取り、バックエンドにリクエストを送信
- **UI状態管理**: ローディング状態、エラー表示などのUI状態の管理

### 例外

- **UI固有の処理**: アニメーション、レイアウト調整など、純粋にUIに関わる処理はフロントエンドで実行
- **クライアント側の最適化**: 大量データの仮想スクロールなど、パフォーマンス最適化のための処理はフロントエンドで実行可能

### 実装時の判断基準

1. その処理はバックエンドで実行できるか？
2. バックエンドで実行することで、テストしやすさ、再利用性、保守性が向上するか？
3. フロントエンドで実行する明確な理由（パフォーマンス、UXなど）があるか？

上記の質問に「はい」と答えられる場合は、バックエンドで実装することを優先する。

## If Statement Refactoring Guidelines

**基本方針**: if文やtry-catch、3項演算子はすべて分岐関数にしてください。条件式はすべて要約関数に置き換え、staticにできる関数はなるべくstaticにする。分岐関数内では if-else を使用し、3項演算子は使用しない。

### 原則

- **条件抽出**: 条件式を独立した関数に抽出する。これにより、条件の再利用性とテストしやすさが向上する。
- **関数形式**: 抽出する関数は以下の形式に従う。

    ```php
    function hoge(int $a, int $b): bool {
      return $a === $b;
    }
    ```

    リファクタリング例:

    ```php
    function hoge(): int {
      if (condition()) {
        return foo();
      } else {
        return bar();
      }
    }
    ```

    を以下のようにリファクタリング:

    ```php
    function condition(): bool {
      // 条件式をここに抽出
    }

    function hoge(): int {
      return condition() ? foo() : bar();
    }
    ```

    または、より複雑な場合:

    ```php
    function hoge($condition, $callback): int {
      if ($condition) {
        return callback();
      } else {
        return bar();
      }
    }
    ```

    を:

    ```php
    function isConditionMet($condition): bool {
      return $condition;
    }

    function hoge($condition, $callback): int {
      return isConditionMet($condition) ? $callback() : bar();
    }
    ```

    try-catchの場合:

    ```php
    function hoge(): int {
      try (condition()) {
        return foo();
      } catch(Throwable $e) {
        throwBarError();
      }
    }
    ```

    を:

    ```php
    function executeWithErrorHandling(): int {
      try {
        return condition() ? foo() : throwBarError();
      } catch(Throwable $e) {
        throwBarError();
      }
    }

    function hoge(): int {
      return executeWithErrorHandling();
    }
    ```

    または、voidを返す場合:

    ```php
    function fooIf($condition): void {
      if ($condition) {
        foo();
      }
    }
    ```

    3項演算子の場合:

    ```php
    function hoge(): int {
      return condition() ? foo() : bar();
    }
    ```

    を以下のようにリファクタリング:

    ```php
    function selectValue($condition): int {
      if ($condition) {
        return foo();
      } else {
        return bar();
      }
    }

    function condition(): bool {
      // 条件式をここに抽出
    }

    function hoge(): int {
      return selectValue(condition());
    }
    ```

- **Static化**: インスタンス状態に依存しない関数はstaticメソッドとして定義する。これにより、メモリ使用量が最適化され、パフォーマンスが向上する。
- **スコープ制限**: 分岐関数の各スコープには関数を1つだけ書ける。必要なければreturnは省略できる。

### 実装時の判断基準

1. if文の条件式が直接書かれているか？（直接書かれている場合は関数に抽出する）
1. 条件式が変数に直接代入されているか？（変数に直接代入されている場合は関数に抽出する）
1. 関数がインスタンスのプロパティやメソッドに依存するか？（依存しない場合はstaticにする）

上記の基準に該当する場合、このガイドラインを適用する。

## Storybook Guidelines

### Purpose

Storybook は UI コンポーネントの開発とドキュメント化を支援するツールです。Inertia.js ページコンポーネントの開発時には、Storybook を使用してコンポーネントを独立して開発・テストできます。

### File Organization

- **ストーリーファイルの配置**: `stories/` ディレクトリに配置
- **ページ構造との対応**: ページコンポーネントの構造に対応するパターンで配置
    - 例: `stories/Project/Index.stories.tsx` は `resources/js/pages/Project/Index.tsx` に対応
- **モックファイル**: Inertia.js などのモックは `stories/mocks/` に配置
    - 例: `stories/mocks/inertia.tsx` で Inertia.js をモック

### Usage in Frontend Development

1. **新規ページコンポーネント開発時**:
    - まず Storybook ストーリーファイルを作成（`stories/{Feature}/{Page}.stories.tsx`）
    - 正常状態、空状態、ローディング状態、エラー状態などのストーリーを定義
    - Inertia.js のモックを設定（`stories/mocks/inertia.tsx` を使用）
    - Storybook でコンポーネントを開発・確認

2. **既存コンポーネントの更新時**:
    - 対応するストーリーファイルを更新
    - 新しい状態やバリアントがある場合はストーリーを追加

3. **開発フロー**:
    - `npm run storybook` で Storybook を起動
    - ブラウザで `http://localhost:6006` にアクセス
    - コンポーネントを独立して開発・テスト
    - 完成したら実際のアプリケーションで統合テスト

### Inertia.js Mocking

- Inertia.js のモックは `.storybook/main.ts` で設定済み
- `@inertiajs/react` は自動的に `stories/mocks/inertia.tsx` にマッピングされる
- ストーリーファイル内で `setProcessing()` などのヘルパー関数を使用可能

### Best Practices

- **状態の網羅**: 正常状態、空状態、ローディング状態、エラー状態を必ず定義
- **型安全性**: TypeScript の型定義を活用して props の型を明確化
- **ドキュメント化**: `tags: ['autodocs']` を使用して自動ドキュメント生成
- **アクセシビリティ**: `@storybook/addon-a11y` を使用してアクセシビリティを確認

## プロセス関数ガイドライン

**基本方針**: 要約関数や分岐関数以外の関数はパイプ演算子を使ったプロセス関数として書いてください。ただし、メソッドチェーンはパイプ演算子に置き換える必要はないです。

### 原則

- 要約関数や分岐関数以外の関数は、パイプ演算子（|>）を使ってプロセス関数として書く。これにより、データの流れが明確になり、関数の可読性と保守性が向上する。
- メソッドチェーンはパイプ演算子に置き換える必要はない。既存のメソッドチェーンを維持しても良い。

### 実装例

```php
function hoge($a, $b) {
  return foo($a, $b)
  |> getAOrB(...)
  |> toFooObject(...)
  |> validateFooObject(...);
}
```

または、メソッドチェーンを使用:

```php
function hoge($collection) {
  return $collection
    ->map(...)
    ->filter(...)
    ->toArray();
}
```

### 実装時の判断基準

1. その関数は要約関数か分岐関数か？（要約関数や分岐関数でない場合はパイプ演算子を使う）
2. パイプ演算子を使うことで、関数の流れが明確になるか？

上記の基準に該当する場合、このガイドラインを適用する。

## 無名関数ガイドライン

**基本方針**: 無名関数は関数にしてください。

### 原則

- 無名関数（クロージャーやアロー関数）を名前付き関数に置き換える。これにより、コードの可読性、再利用性、テストしやすさが向上する。
- 可能な限りpublic staticメソッドとして定義する。インスタンス状態に依存する場合のみprivateメソッドとする。

### 例外

- callableを返す関数内でのみ、fnを使用することを許可する。この場合、無名関数を避けることが難しいため。

    ```php
    public static function getMonthTotalMapper(array $monthValues): callable
    {
      return fn(int $month) => self::buildMonthTotalPair($month, $monthValues);
    }
    ```

### 実装例

```php
// 変更前
$collection->mapWithKeys(fn($month) => self::buildMonthTotalPair($month, $monthValues));

// 変更後
$collection->mapWithKeys(self::getMonthTotalMapper($monthValues));

public static function getMonthTotalMapper(array $monthValues): callable
{
    return fn(int $month) => self::buildMonthTotalPair($month, $monthValues);
}

// 変更前
$collection->map(function ($item) {
    return $item['value'];
});

// 変更後
public static function mapToValue(array $item): mixed
{
    return $item['value'];
}

$collection->map(self::mapToValue(...));

// 変更前
$collection->map(fn($month) => self::buildMonthData($month, $userMonthData));

// 変更後
$collection->map(self::getBuildMonthDataMapper($userMonthData));

public static function getBuildMonthDataMapper(Collection $userMonthData): callable
{
    return fn($month) => self::buildMonthData($month, $userMonthData);
}
```

### 実装時の判断基準

1. 無名関数が使用されているか？（使用されている場合は名前付き関数に置き換える）
2. 関数が再利用可能か？（再利用可能な場合は関数に抽出する）

上記の基準に該当する場合、このガイドラインを適用する。

## クロージャー書き方ガイドライン

**基本方針**: クロージャーの書き方が古いです、スプレッド演算子を使うことで`[$this, 'hoge']`の代わりに`$this->hoge(...)`と書けます。

### 原則

- PHP 8.1以降の第一級callable構文を使用する。これにより、コードがよりモダンで読みやすくなる。
- インスタンスメソッドの場合は `$this->method(...)` を使用。
- staticメソッドの場合は `ClassName::method(...)` を使用。

### 実装例

```php
// 変更前
$collection->map([$this, 'mapToValue']);

// 変更後
$collection->map($this->mapToValue(...));
```

### 実装時の判断基準

1. `[$this, 'method']` や `[self::class, 'method']` が使用されているか？（使用されている場合は第一級callable構文に置き換える）
2. PHP 8.1以降を使用しているか？（使用している場合はこの構文を適用する）

上記の基準に該当する場合、このガイドラインを適用する。

## 参照渡しガイドライン

**基本方針**: 引数での参照渡しは禁止です。

### 原則

- 関数やメソッドの引数に `&` を付けて参照渡しを行うことを禁止する。これにより、コードの可読性と予測性が向上する。
- 参照渡しが必要な場合は、戻り値やオブジェクトのプロパティを使用する。

### 実装例

```php
// 変更前
public static function addUserDataToMonthData(array &$monthData, array $data, int $month): void
{
    // ...
}

// 変更後
public static function addUserDataToMonthData(array $monthData, array $data, int $month): array
{
    // ... 処理 ...
    return $monthData;
}
```

### 実装時の判断基準

1. 引数に `&` が付いているか？（付いている場合は参照渡しを避ける）
2. 参照渡しを避けることで、関数が純粋になるか？（純粋になる場合は適用する）

上記の基準に該当する場合、このガイドラインを適用する。
