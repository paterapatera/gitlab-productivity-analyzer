# Testing Standards

## Philosophy

- **動作をテストする、実装をテストしない**: ビジネスロジックと動作に焦点を当て、内部実装の詳細に依存しない
- **高速で信頼性の高いテスト**: モックは最小限に、外部依存のみをモック
- **重要なパスを深くカバー**: 100%のカバレッジを追求せず、クリティカルなパスを優先

## Organization

### Backend (PHP/Pest)

**Location**: `/tests/`  
**Structure**: Feature テストと Unit テストを分離

- **Feature Tests** (`tests/Feature/`): 複数レイヤーを統合したテスト。データベースを使用する場合は `RefreshDatabase` トレイトを使用
- **Unit Tests** (`tests/Unit/`): 単一のクラスやメソッドをテスト。外部依存はモック

**Naming**:
- ファイル: `{ClassName}Test.php`
- テスト関数: `test('説明', function () { ... })` または `it('説明', function () { ... })`
- グループ化: `describe('グループ名', function () { ... })`

**Example**:
```php
describe('ProjectController', function () {
    test('プロジェクト一覧を取得してInertia.jsページを返却する', function () {
        // Arrange
        $repository = getRepository();
        $repository->save(createProject(1, 'group/project1'));
        
        // Act
        $response = $this->withoutVite()->get('/projects');
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Project/Index')
            ->has('projects', 1)
        );
    });
});
```

### Frontend (TypeScript/Vitest)

**Location**: `/resources/js/test/`  
**Structure**: ページコンポーネントに対応するテストファイル

- テストファイル: `{ComponentName}.test.tsx` または `{ComponentName}.spec.tsx`
- セットアップ: `resources/js/test/setup.ts` で共通設定

**Naming**:
- ファイル: `{ComponentName}.test.tsx`
- テスト: `it('説明', () => { ... })` または `describe('グループ名', () => { ... })`

**Example**:
```typescript
describe('Project/Index', () => {
    it('プロジェクト一覧を表示する', () => {
        // Arrange
        const projects = [{ id: 1, name_with_namespace: 'group/project1' }];
        
        // Act
        render(<Index projects={projects} />);
        
        // Assert
        expect(screen.getByText('group/project1')).toBeInTheDocument();
    });
});
```

## Test Types

### Unit Tests
- **目的**: 単一のクラス、メソッド、関数をテスト
- **モック**: 外部依存（データベース、API、ファイルシステム）をモック
- **速度**: 非常に高速
- **例**: ドメインエンティティ、値オブジェクト、ユーティリティ関数

### Feature Tests
- **目的**: 複数のレイヤーを統合したテスト
- **モック**: 外部サービス（GitLab API等）のみをモック、データベースは実際に使用
- **速度**: 中程度（データベースアクセスを含む）
- **例**: コントローラー、サービス、リポジトリの統合

### Component Tests (Frontend)
- **目的**: React コンポーネントの動作をテスト
- **モック**: Inertia.js、外部API、ブラウザAPI
- **速度**: 高速（jsdom環境）
- **例**: ページコンポーネント、UIコンポーネント

## Structure (AAA Pattern)

すべてのテストは Arrange-Act-Assert パターンに従う:

```php
test('説明', function () {
    // Arrange: テストデータと環境の準備
    $project = createProject(1, 'group/project');
    
    // Act: テスト対象の実行
    $result = $service->execute($project);
    
    // Assert: 結果の検証
    expect($result)->toBe(expected);
});
```

## Mocking & Data

### Backend (Pest/Mockery)
- **外部依存のモック**: リポジトリ、外部API、サービスをモック
- **ヘルパー関数**: `tests/Helpers.php` にテスト用のファクトリー関数を配置
- **データベース**: Feature テストでは `RefreshDatabase` トレイトを使用してデータベースをリセット

**Example**:
```php
$mockGetProjects = Mockery::mock(GetProjects::class);
$mockGetProjects->shouldReceive('execute')
    ->once()
    ->andReturn(collect([createProject(1, 'group/project')]));

app()->instance(GetProjects::class, $mockGetProjects);
```

### Frontend (Vitest)
- **Inertia.js のモック**: `vi.mock('@inertiajs/react')` でモック
- **ブラウザAPI**: `window.matchMedia` などのブラウザAPIを `setup.ts` でモック
- **テストデータ**: テスト内で直接定義、またはファクトリー関数を使用

**Example**:
```typescript
vi.mock('@inertiajs/react', () => ({
    useForm: vi.fn(() => ({
        post: vi.fn(),
        processing: false,
    })),
}));
```

## Test Helpers

### Backend
- **`createProject()`**: テスト用の Project エンティティを作成（`tests/Helpers.php`）
- **`getRepository()`**: リポジトリインスタンスを取得
- **`mockSyncProjects()`**: SyncProjects サービスをモック

### Frontend
- **`setup.ts`**: 共通のセットアップ（`window.matchMedia` のモック、クリーンアップ）

## Coverage

- **目標**: 重要なビジネスロジックとクリティカルパスを優先
- **ドメイン層**: 高いカバレッジを目指す（エンティティ、値オブジェクト）
- **アプリケーション層**: ユースケースの主要なパスをカバー
- **プレゼンテーション層**: 主要なエッジケースをカバー

## Storybook Integration

Storybook のストーリーファイル（`stories/`）は、コンポーネントの開発とドキュメント化を支援。Vitest の Storybook プロジェクトでストーリーをテストとして実行可能。

---
_テストの組織化とパターンを記述。すべてのテストファイルを列挙するものではない_
