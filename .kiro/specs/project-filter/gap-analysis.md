# Implementation Gap Analysis

## Current State Investigation

### Existing Assets

#### Frontend Components
- **`resources/js/pages/Commit/Index.tsx`**: コミット収集画面のページコンポーネント
  - Radix UI の `Select` コンポーネントを使用してプロジェクト選択を実装
  - `projects` プロップとしてプロジェクト一覧を受け取る
  - `projectId` を state で管理し、フォーム送信時に使用
  - Inertia.js の `Form` コンポーネントと統合

- **`resources/js/components/ui/select.tsx`**: Radix UI Select プリミティブのラッパー
  - `@radix-ui/react-select` を使用
  - 検索機能は実装されていない
  - 既存のデザインシステム（Tailwind CSS）と統合済み

- **`resources/js/components/ui/input.tsx`**: 入力フィールドコンポーネント
  - 標準的な input 要素のラッパー
  - 検索入力フィールドとして使用可能

#### Backend
- **`app/Presentation/Controller/CommitController.php`**: コミット収集コントローラー
  - `collectShow()` メソッドでプロジェクト一覧を取得
  - `ProjectRepository` から全プロジェクトを取得してフロントエンドに渡す
  - 既存の実装で十分（検索はクライアント側で実行）

#### Type Definitions
- **`resources/js/types/commit.d.ts`**: 型定義
  - `CommitProject` インターフェース: `{ id: number, name_with_namespace: string }`
  - `CommitPageProps` インターフェース: `projects: CommitProject[]`

### Existing Patterns

#### Component Structure
- Radix UI プリミティブをラップした UI コンポーネントパターン
- `data-slot` 属性を使用したスタイリング
- Tailwind CSS クラスによるスタイリング
- TypeScript strict mode による型安全性

#### State Management
- React の `useState` フックを使用
- フォーム状態は Inertia.js の `Form` コンポーネントで管理

#### Error Handling
- Inertia.js の `errors` プロップでバリデーションエラーを表示
- `aria-invalid` 属性でアクセシビリティ対応

## Requirements Feasibility Analysis

### Technical Needs from Requirements

#### Requirement 1: プロジェクト検索入力機能
- **必要な機能**: 検索入力フィールドの表示、入力値の保持、リアルタイム表示
- **既存資産**: `Input` コンポーネントが利用可能
- **ギャップ**: 検索入力フィールドをドロップダウン内に配置する必要がある

#### Requirement 2: プロジェクトフィルタリング機能
- **必要な機能**: 入力文字列によるプロジェクト一覧のフィルタリング
- **既存資産**: `projects` プロップとして全プロジェクトが利用可能
- **ギャップ**: フィルタリングロジックの実装が必要（クライアント側）

#### Requirement 3: プロジェクト選択機能の維持
- **必要な機能**: 既存の選択・バリデーション・フォーム送信機能の維持
- **既存資産**: 既存の実装が利用可能
- **ギャップ**: 検索機能を追加しても既存機能を維持する必要がある

#### Requirement 4: UI/UX要件
- **必要な機能**: ドロップダウン表示、スクロール、Escapeキーでの閉じる操作
- **既存資産**: Radix UI の `Select` コンポーネントが一部の機能を提供
- **ギャップ**: 検索機能を持つコンポーネントが必要

### Missing Capabilities

1. **検索可能なセレクトコンポーネント**: Radix UI の `Select` は検索機能を持たない
2. **Popover コンポーネント**: 検索入力フィールドとプロジェクト一覧を表示するためのコンポーネントが必要
3. **フィルタリングロジック**: プロジェクト名による部分一致検索の実装が必要

### Constraints

- **Radix UI の制約**: `@radix-ui/react-select` は検索機能をサポートしていない
- **既存のデザインシステム**: Tailwind CSS と Radix UI のパターンに従う必要がある
- **型安全性**: TypeScript strict mode により、型定義が必要

### Research Needed

1. **Radix UI の Popover コンポーネント**: `@radix-ui/react-popover` の使用方法と既存のデザインシステムとの統合方法
2. **検索可能なセレクトの実装パターン**: Radix UI を使用した検索可能なセレクトコンポーネントのベストプラクティス
3. **パフォーマンス最適化**: 大量のプロジェクト（100件以上）がある場合のフィルタリングパフォーマンス

## Implementation Approach Options

### Option A: Extend Existing Select Component

**概要**: 既存の `Select` コンポーネントを拡張して検索機能を追加

**実装内容**:
- `resources/js/components/ui/select.tsx` に検索入力フィールドを追加
- `SelectContent` 内に検索入力フィールドを配置
- フィルタリングロジックを `Select` コンポーネント内に実装

**互換性評価**:
- ❌ Radix UI の `Select` プリミティブは検索機能をサポートしていない
- ❌ 既存の `Select` コンポーネントの構造を大きく変更する必要がある
- ❌ 既存の使用箇所への影響が大きい

**複雑性と保守性**:
- ❌ 単一責任の原則に違反する可能性（選択機能と検索機能の混在）
- ❌ コンポーネントの複雑性が増加
- ❌ 既存の `Select` コンポーネントの再利用性が低下

**Trade-offs**:
- ❌ Radix UI の制約により実装が困難
- ❌ 既存の使用箇所への影響が大きい
- ❌ 保守性が低下する可能性

### Option B: Create New Combobox Component

**概要**: 検索可能なセレクトコンポーネント（Combobox）を新規作成

**実装内容**:
- `resources/js/components/ui/combobox.tsx` を新規作成
- Radix UI の `Popover` と `Input` を組み合わせて実装
- フィルタリングロジックをコンポーネント内に実装
- `Commit/Index.tsx` で既存の `Select` を `Combobox` に置き換え

**統合ポイント**:
- `resources/js/pages/Commit/Index.tsx` で `Combobox` をインポート
- 既存の `projectId` state と `onValueChange` ハンドラーを維持
- 既存のバリデーション機能（`errors.project_id`）を維持

**責任の境界**:
- `Combobox` コンポーネント: 検索入力、フィルタリング、プロジェクト一覧の表示、選択処理
- `Commit/Index.tsx`: フォーム状態管理、バリデーションエラー表示、フォーム送信

**Trade-offs**:
- ✅ 関心の分離が明確
- ✅ 既存の `Select` コンポーネントに影響を与えない
- ✅ テストが容易（独立したコンポーネント）
- ✅ 他の画面でも再利用可能
- ❌ 新しいコンポーネントファイルが必要
- ❌ Radix UI の `Popover` の追加インストールが必要

### Option C: Hybrid Approach

**概要**: 既存の `Input` コンポーネントを拡張し、新しい `Combobox` コンポーネントを作成

**実装内容**:
- `resources/js/components/ui/combobox.tsx` を新規作成
- 既存の `Input` コンポーネントを検索入力フィールドとして使用
- Radix UI の `Popover` を使用してドロップダウンを実装
- フィルタリングロジックを `Combobox` コンポーネント内に実装

**段階的実装**:
1. **Phase 1**: `Combobox` コンポーネントの作成と基本機能の実装
2. **Phase 2**: `Commit/Index.tsx` での統合とテスト
3. **Phase 3**: 既存のデザインシステムとの統合確認

**リスク軽減**:
- 既存の `Select` コンポーネントは変更しない
- 新しいコンポーネントは独立してテスト可能
- 段階的なロールアウトが可能

**Trade-offs**:
- ✅ 既存コンポーネントへの影響を最小限に抑制
- ✅ 段階的な実装が可能
- ✅ 既存の `Input` コンポーネントを再利用
- ❌ 複数のコンポーネントの統合が必要
- ❌ 実装の複雑性が中程度

## Implementation Complexity & Risk

### Effort: **M (3-7 days)**

**根拠**:
- Radix UI の `Popover` の追加インストールと統合（1日）
- `Combobox` コンポーネントの実装（2-3日）
- フィルタリングロジックの実装（1日）
- `Commit/Index.tsx` での統合とテスト（1-2日）

### Risk: **Medium**

**根拠**:
- Radix UI の `Popover` は既知のパターンで、ドキュメントが充実している
- 既存のデザインシステム（Tailwind CSS）との統合は明確
- 既存の `Select` コンポーネントに影響を与えないため、リスクが低い
- フィルタリングロジックは単純な文字列マッチングのため、実装が容易

## Recommendations for Design Phase

### Preferred Approach: **Option B (Create New Combobox Component)**

**推奨理由**:
1. **関心の分離**: 検索機能を持つコンポーネントを独立させることで、既存の `Select` コンポーネントの責務を明確に保つ
2. **再利用性**: 他の画面でも検索可能なセレクトが必要な場合に再利用可能
3. **保守性**: 独立したコンポーネントとして実装することで、テストと保守が容易
4. **既存コードへの影響**: 既存の `Select` コンポーネントに影響を与えない

### Key Decisions for Design Phase

1. **Radix UI Popover の統合方法**:
   - `@radix-ui/react-popover` のインストールと使用方法
   - 既存のデザインシステム（Tailwind CSS）との統合方法
   - `Popover` と `Input` の組み合わせパターン

2. **フィルタリングロジックの実装場所**:
   - `Combobox` コンポーネント内で実装するか、カスタムフックに分離するか
   - パフォーマンス最適化の必要性（大量のプロジェクトがある場合）

3. **既存のデザインシステムとの統合**:
   - `Combobox` コンポーネントのスタイリングを既存の `Select` コンポーネントと一貫性を保つ方法
   - アイコンの配置とスタイリング（検索アイコンなど）

4. **空状態の表示**:
   - "該当するプロジェクトが見つかりません" メッセージの表示方法
   - 空状態のスタイリング

### Research Items to Carry Forward

1. **Radix UI Popover の使用方法**:
   - `@radix-ui/react-popover` の API と使用方法
   - `Popover` と `Input` の組み合わせパターン
   - キーボード操作（Escapeキー）の実装方法

2. **検索可能なセレクトのベストプラクティス**:
   - Radix UI を使用した検索可能なセレクトコンポーネントの実装パターン
   - アクセシビリティの考慮事項（要件には含まれていないが、既存のパターンに従う）

3. **パフォーマンス最適化**:
   - 大量のプロジェクト（100件以上）がある場合のフィルタリングパフォーマンス
   - 仮想スクロールの必要性の検討

## Summary

既存のコードベースでは、Radix UI の `Select` コンポーネントを使用してプロジェクト選択を実装していますが、検索機能は実装されていません。要件を満たすためには、検索可能なセレクトコンポーネント（Combobox）を新規作成する必要があります。

**推奨アプローチ**: Option B（新しい Combobox コンポーネントの作成）を推奨します。既存の `Select` コンポーネントに影響を与えず、関心の分離を保ちながら、再利用可能なコンポーネントとして実装できます。

**実装の複雑性**: 中程度（3-7日）
**リスク**: 中程度（既知のパターンと技術スタックを使用）

設計フェーズでは、Radix UI の `Popover` の統合方法、フィルタリングロジックの実装場所、既存のデザインシステムとの統合方法について詳細な設計を行う必要があります。
