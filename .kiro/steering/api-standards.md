# API Standards

## Philosophy

- **予測可能な設計**: リソース指向の設計を優先し、一貫性のあるエンドポイント構造
- **明示的な契約**: インターフェースとDTOで明確な契約を定義
- **セキュリティファースト**: 認証を最初に検証し、最小権限の原則を適用

## External API Integration Pattern

### Port and Adapters Pattern

外部API統合は **Ports and Adapters パターン**（Hexagonal Architecture）に従う:

- **Port** (`/app/Application/Port/`): 外部APIとのインターフェースを定義
- **Adapter** (`/app/Infrastructure/`): Port の実装。具体的なAPIクライアント

**Example**:
```php
// Port: インターフェース定義
interface GitApi {
    public function getProjects(): Collection;
}

// Adapter: 実装
class GitLabApiClient implements GitApi {
    public function getProjects(): Collection {
        // GitLab API呼び出し
    }
}
```

### Configuration Pattern

外部APIの設定は `config/services.php` に定義し、環境変数から読み込む:

```php
// config/services.php
'gitlab' => [
    'base_url' => env('GITLAB_BASE_URL'),
    'token' => env('GITLAB_TOKEN'),
],
```

クライアントは `fromConfig()` メソッドで設定からインスタンスを作成:

```php
public static function fromConfig(): self {
    $baseUrl = config('services.gitlab.base_url');
    $token = config('services.gitlab.token');
    
    if (empty($baseUrl) || empty($token)) {
        throw new GitLabApiException('Configuration is missing');
    }
    
    return new self($baseUrl, $token);
}
```

## Request/Response Patterns

### Authentication

外部APIへの認証は HTTP ヘッダーで送信:

```php
Http::withHeaders([
    'PRIVATE-TOKEN' => $token,  // GitLab API
    // または
    'Authorization' => "Bearer {$token}",  // 一般的なBearer認証
])->get($url);
```

### Pagination

ページネーションは API の仕様に従う。GitLab API の例:

```php
$page = 1;
$totalPages = 1;

do {
    $response = Http::get($url, ['page' => $page, 'per_page' => 100]);
    $totalPages = (int) $response->header('X-Total-Pages') ?: 1;
    $page++;
} while ($page <= $totalPages);
```

### Rate Limiting

レート制限エラー（429）の処理:

```php
if ($response->status() === 429) {
    $retryAfter = (int) $response->header('Retry-After') ?: 1;
    $delay = min($retryAfter * (2 ** ($page - 1)), 60); // 指数バックオフ、最大60秒
    sleep($delay);
    continue; // 同じページを再試行
}
```

## Error Handling

### Exception Types

外部API統合では、ドメイン固有の例外を使用:

```php
namespace App\Infrastructure\GitLab\Exceptions;

class GitLabApiException extends Exception
{
}
```

### Error Mapping

HTTPステータスコードを適切な例外にマッピング:

```php
if ($response->status() === 401) {
    throw new GitLabApiException('Authentication failed');
}

if (!$response->successful()) {
    throw new GitLabApiException(
        "API error: {$response->status()} - {$response->body()}"
    );
}
```

### Connection Errors

接続エラーは適切にラップ:

```php
try {
    $response = Http::get($url);
} catch (ConnectionException $e) {
    throw new GitLabApiException(
        "Connection error: {$e->getMessage()}",
        0,
        $e
    );
}
```

## Data Transformation

### API Response to Domain Entity

外部APIのレスポンスをドメインエンティティに変換:

```php
protected function convertToProject(array $projectData): Project
{
    return new Project(
        id: new ProjectId((int) $projectData['id']),
        nameWithNamespace: new ProjectNameWithNamespace($projectData['name_with_namespace']),
        description: new ProjectDescription($projectData['description'] ?? null),
        defaultBranch: new DefaultBranch($projectData['default_branch'] ?? null)
    );
}
```

**原則**: 
- 外部APIのデータ構造に依存しないドメインモデルを使用
- 値オブジェクトで型安全性を確保
- null 値の処理を明示的に定義

## Testing External APIs

### Mocking HTTP Requests

Laravel の `Http::fake()` を使用して外部APIをモック:

```php
Http::fake([
    'gitlab.example.com/api/v4/projects*' => Http::response([
        createProjectData(1, 'group/project', 'Description', 'main'),
    ], 200, ['X-Total-Pages' => '1', 'X-Page' => '1']),
]);

$client = new GitLabApiClient('https://gitlab.example.com', 'test-token');
$projects = $client->getProjects();

expect($projects)->toHaveCount(1);
```

### Asserting Requests

送信されたリクエストを検証:

```php
Http::assertSent(function ($request) {
    return $request->hasHeader('PRIVATE-TOKEN', 'test-token')
        && $request->url() === 'https://gitlab.example.com/api/v4/projects';
});
```

## Service Provider Binding

Port と Adapter のバインディングは `AppServiceProvider` で定義:

```php
public function register(): void
{
    $this->app->bind(GitApi::class, function ($app) {
        return GitLabApiClient::fromConfig();
    });
}
```

これにより、依存性注入で Port インターフェースを使用でき、実装を簡単に切り替え可能。

---
_外部API統合のパターンと決定を記述。すべてのAPIエンドポイントを列挙するものではない_
