# Error Handling Standards

## Philosophy

- **失敗を早期に検出**: 可能な限り早期に失敗を検出し、システム境界で適切に処理
- **一貫したエラー形式**: スタック全体で一貫したエラー形式（人間と機械の両方が読み取り可能）
- **既知のエラーは発生源近くで処理**: 未知のエラーはグローバルハンドラーに委譲

## Classification

### Client Errors (4xx)
- **入力検証エラー**: リクエストの形式や値が不正
- **認証エラー**: 認証情報が不足または無効
- **権限エラー**: 認証済みだが権限が不足
- **ビジネスルール違反**: ビジネスロジックの制約違反（例: 409 Conflict）

### Server Errors (5xx)
- **システムエラー**: 予期しない例外、データベースエラー
- **外部サービスエラー**: 外部APIの呼び出し失敗
- **設定エラー**: 設定の不足や不正

### External Service Errors
外部サービスのエラーは、適切に分類してマッピング:
- **認証エラー** (401) → 4xx
- **レート制限** (429) → 5xx（リトライ可能）
- **サーバーエラー** (5xx) → 5xx
- **接続エラー** → 5xx

## Error Shape

### Domain Exceptions

ドメイン固有の例外クラスを定義:

```php
namespace App\Infrastructure\GitLab\Exceptions;

class GitLabApiException extends Exception
{
}
```

### DTO for Error Results

エラーを含む結果を返す場合は、DTOを使用:

```php
readonly class SyncResult
{
    public function __construct(
        public int $syncedCount,
        public int $deletedCount,
        public bool $hasErrors = false,
        public ?string $errorMessage = null
    ) {
    }
}
```

## Propagation Patterns

### Service Layer

サービス層では、例外をキャッチして結果DTOに変換:

```php
public function execute(): SyncResult
{
    try {
        $projects = $this->getProjects->execute();
        $this->persistProjects->execute($projects);
        
        return new SyncResult(
            syncedCount: $projects->count(),
            deletedCount: 0,
            hasErrors: false
        );
    } catch (\Exception $e) {
        return new SyncResult(
            syncedCount: 0,
            deletedCount: 0,
            hasErrors: true,
            errorMessage: $e->getMessage()
        );
    }
}
```

### Presentation Layer

プレゼンテーション層（コントローラー）では、HTTPステータスコードとメッセージに変換:

```php
public function index(Request $httpRequest): Response
{
    try {
        $projects = $this->repository->findAll();
        return Inertia::render('Project/Index', $response->toArray());
    } catch (\Exception $e) {
        abort(500, 'プロジェクト一覧の取得に失敗しました。');
    }
}
```

### Redirect with Flash Messages

リダイレクトが必要な場合は、セッションにフラッシュメッセージを設定:

```php
public function sync(): RedirectResponse
{
    try {
        $result = $this->syncProjects->execute();
        
        if ($result->hasErrors) {
            return redirect()->route('projects.index')
                ->with('error', $result->errorMessage ?? '同期処理中にエラーが発生しました。');
        }
        
        return redirect()->route('projects.index')
            ->with('success', "同期が完了しました。同期: {$result->syncedCount}件");
    } catch (\Exception $e) {
        abort(500, '同期処理に失敗しました。');
    }
}
```

## Error Wrapping

### External API Errors

外部APIのエラーは、ドメイン例外でラップ:

```php
try {
    $response = Http::get($url);
} catch (ConnectionException $e) {
    throw new GitLabApiException(
        "GitLab API connection error: {$e->getMessage()}",
        0,
        $e  // 元の例外を保持
    );
}
```

### Configuration Errors

設定エラーは、明確なメッセージと共に例外をスロー:

```php
if (empty($baseUrl) || empty($token)) {
    throw new GitLabApiException(
        'GitLab API configuration is missing. Please set GITLAB_BASE_URL and GITLAB_TOKEN in your .env file.'
    );
}
```

## Frontend Error Display

### Flash Messages

バックエンドからフラッシュメッセージを受け取り、フロントエンドで表示:

```typescript
export default function Index({ projects, error, success }: ProjectPageProps) {
    return (
        <>
            {error && (
                <Alert variant="destructive">
                    <AlertTitle>エラー</AlertTitle>
                    <AlertDescription>{error}</AlertDescription>
                </Alert>
            )}
            
            {success && (
                <Alert>
                    <AlertTitle>成功</AlertTitle>
                    <AlertDescription>{success}</AlertDescription>
                </Alert>
            )}
        </>
    );
}
```

### Loading States

非同期処理中はローディング状態を表示:

```typescript
const { post, processing } = useForm({});

<Button onClick={handleSync} disabled={processing}>
    {processing ? (
        <>
            <RefreshCwIcon className="animate-spin" />
            同期中...
        </>
    ) : (
        <>
            <RefreshCwIcon />
            同期
        </>
    )}
</Button>
```

## Retry Strategy

### When to Retry

リトライは以下の条件でのみ実行:
- **ネットワークエラー**: 接続タイムアウト、一時的な接続エラー
- **一時的なサーバーエラー**: 5xx エラー（特に 503, 429）
- **冪等性が保証されている操作**: GET、DELETE、冪等なPUT/PATCH

### Retry Implementation

指数バックオフと最大遅延時間を設定:

```php
if ($response->status() === 429) {
    $retryAfter = (int) $response->header('Retry-After') ?: 1;
    $delay = min($retryAfter * (2 ** ($page - 1)), 60); // 最大60秒
    sleep($delay);
    continue; // 同じページを再試行
}
```

**原則**:
- 4xx エラーはリトライしない（クライアントエラー）
- ビジネスエラーはリトライしない
- 非冪等な操作はリトライしない

## Testing Error Handling

### Exception Testing

例外が正しくスローされることをテスト:

```php
test('設定が不足している場合に例外をスローする', function () {
    config(['services.gitlab.base_url' => '']);
    config(['services.gitlab.token' => '']);
    
    expect(fn () => GitLabApiClient::fromConfig())
        ->toThrow(GitLabApiException::class);
});
```

### Error Response Testing

エラー時のレスポンスをテスト:

```php
test('リポジトリエラー時に500エラーを返す', function () {
    $mockRepository = Mockery::mock(ProjectRepository::class);
    $mockRepository->shouldReceive('findAll')
        ->once()
        ->andThrow(new \Exception('Database connection failed'));
    
    app()->instance(ProjectRepository::class, $mockRepository);
    
    $response = $this->withoutVite()->get('/projects');
    $response->assertStatus(500);
});
```

---
_エラーハンドリングのパターンと決定を記述。すべてのエラーケースを列挙するものではない_
