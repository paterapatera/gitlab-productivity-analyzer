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
