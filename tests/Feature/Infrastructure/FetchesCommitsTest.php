<?php

use App\Domain\Commit;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\ProjectId;
use App\Infrastructure\GitLab\Exceptions\GitLabApiException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

require_once __DIR__.'/Helpers.php';

// テスト用のモッククラス
class TestCommitApiClient
{
    use \App\Infrastructure\GitLab\FetchesCommits;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $token
    ) {}

    protected function getGitLabBaseUrl(): string
    {
        return $this->baseUrl;
    }

    protected function getGitLabToken(): string
    {
        return $this->token;
    }

    public function testFetchCommits(ProjectId $projectId, BranchName $branchName, ?\DateTime $sinceDate = null): \Illuminate\Support\Collection
    {
        return $this->fetchCommits($projectId, $branchName, $sinceDate);
    }
}

describe('FetchesCommitsトレイトの機能', function () {
    test('GitLab APIからコミット一覧を取得できる', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response([
                createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2025-01-01T12:00:00Z'),
            ], 200, ['X-Next-Page' => '']),
        ]);

        $client = new TestCommitApiClient('https://gitlab.example.com', 'test-token');
        $commits = $client->testFetchCommits(new ProjectId(1), new BranchName('main'));

        expect($commits)->toBeInstanceOf(\Illuminate\Support\Collection::class);
        expect($commits)->toHaveCount(1);
        expect($commits[0])->toBeInstanceOf(Commit::class);
        expect($commits[0]->id->sha->value)->toBe('a1b2c3d4e5f6789012345678901234567890abcd');
        expect($commits[0]->message->value)->toBe('Initial commit');
    });

    test('PRIVATE-TOKENヘッダーで認証する', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response([], 200, ['X-Next-Page' => '']),
        ]);

        $client = new TestCommitApiClient('https://gitlab.example.com', 'test-token');
        $client->testFetchCommits(new ProjectId(1), new BranchName('main'));

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('PRIVATE-TOKEN', 'test-token');
        });
    });

    test('with_stats=trueパラメータを送信する', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response([], 200, ['X-Next-Page' => '']),
        ]);

        $client = new TestCommitApiClient('https://gitlab.example.com', 'test-token');
        $client->testFetchCommits(new ProjectId(1), new BranchName('main'));

        Http::assertSent(function (Request $request) {
            $data = $request->data();

            return isset($data['with_stats']) && $data['with_stats'] === 'true';
        });
    });

    test('ref_nameパラメータにブランチ名を指定する', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response([], 200, ['X-Next-Page' => '']),
        ]);

        $client = new TestCommitApiClient('https://gitlab.example.com', 'test-token');
        $client->testFetchCommits(new ProjectId(1), new BranchName('main'));

        Http::assertSent(function (Request $request) {
            $data = $request->data();

            return isset($data['ref_name']) && $data['ref_name'] === 'main';
        });
    });

    test('複数ページのコミットを取得できる（x-next-pageヘッダーベース）', function () {
        Http::fake(function (Request $request) {
            $data = $request->data();
            $page = $data['page'] ?? 1;

            if ($page == 1) {
                return Http::response([
                    createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2025-01-01T12:00:00Z'),
                ], 200, ['X-Next-Page' => '2']);
            } else {
                return Http::response([
                    createCommitData('b2c3d4e5f6789012345678901234567890abcdef', 'Commit 2', '2025-01-02T12:00:00Z'),
                ], 200, ['X-Next-Page' => '']);
            }
        });

        $client = new TestCommitApiClient('https://gitlab.example.com', 'test-token');
        $commits = $client->testFetchCommits(new ProjectId(1), new BranchName('main'));

        expect($commits)->toHaveCount(2);
    });

    test('開始日パラメータが指定された場合、sinceパラメータを送信する', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response([], 200, ['X-Next-Page' => '']),
        ]);

        $client = new TestCommitApiClient('https://gitlab.example.com', 'test-token');
        $sinceDate = new \DateTime('2025-01-01 12:00:00', new \DateTimeZone('UTC'));
        $client->testFetchCommits(new ProjectId(1), new BranchName('main'), $sinceDate);

        Http::assertSent(function (Request $request) use ($sinceDate) {
            $sinceParam = $request->data()['since'] ?? null;
            $expectedSince = $sinceDate->format('Y-m-d\TH:i:s\Z');

            return $sinceParam === $expectedSince;
        });
    });

    test('開始日パラメータがnullの場合、sinceパラメータを送信しない', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response([], 200, ['X-Next-Page' => '']),
        ]);

        $client = new TestCommitApiClient('https://gitlab.example.com', 'test-token');
        $client->testFetchCommits(new ProjectId(1), new BranchName('main'), null);

        Http::assertSent(function (Request $request) {
            $data = $request->data();

            return ! isset($data['since']);
        });
    });

    test('統計情報（stats）を含むコミットを正しく変換する', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response([
                createCommitData(
                    sha: 'a1b2c3d4e5f6789012345678901234567890abcd',
                    message: 'Test commit',
                    committedDate: '2025-01-01T12:00:00Z',
                    additions: 100,
                    deletions: 10
                ),
            ], 200, ['X-Next-Page' => '']),
        ]);

        $client = new TestCommitApiClient('https://gitlab.example.com', 'test-token');
        $commits = $client->testFetchCommits(new ProjectId(1), new BranchName('main'));

        expect($commits[0]->additions->value)->toBe(100);
        expect($commits[0]->deletions->value)->toBe(10);
    });

    test('statsオブジェクトが存在しない場合、デフォルト値0を使用する', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response([
                createCommitDataWithoutStats(
                    sha: 'a1b2c3d4e5f6789012345678901234567890abcd',
                    message: 'Test commit',
                    committedDate: '2025-01-01T12:00:00Z'
                ),
            ], 200, ['X-Next-Page' => '']),
        ]);

        $client = new TestCommitApiClient('https://gitlab.example.com', 'test-token');
        $commits = $client->testFetchCommits(new ProjectId(1), new BranchName('main'));

        expect($commits[0]->additions->value)->toBe(0);
        expect($commits[0]->deletions->value)->toBe(0);
    });
});

describe('エラーハンドリング', function () {
    test('認証エラー（401）が発生した場合に例外をスローする', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response(['message' => 'Unauthorized'], 401),
        ]);

        $client = new TestCommitApiClient('https://gitlab.example.com', 'invalid-token');

        expect(fn () => $client->testFetchCommits(new ProjectId(1), new BranchName('main')))
            ->toThrow(GitLabApiException::class);
    });

    test('タイムアウトエラーが発生した場合に例外をスローする', function () {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
        });

        $client = new TestCommitApiClient('https://gitlab.example.com', 'test-token');

        expect(fn () => $client->testFetchCommits(new ProjectId(1), new BranchName('main')))
            ->toThrow(GitLabApiException::class);
    });

    test('APIエラー（500）が発生した場合に例外をスローする', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response(['message' => 'Internal Server Error'], 500),
        ]);

        $client = new TestCommitApiClient('https://gitlab.example.com', 'test-token');

        expect(fn () => $client->testFetchCommits(new ProjectId(1), new BranchName('main')))
            ->toThrow(GitLabApiException::class);
    });

    test('429エラー時に指数バックオフでリトライする', function () {
        $attempts = 0;

        Http::fake(function () use (&$attempts) {
            $attempts++;
            if ($attempts < 3) {
                return Http::response(['message' => 'Too Many Requests'], 429, ['Retry-After' => '1']);
            }

            return Http::response([
                createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'Test commit', '2025-01-01T12:00:00Z'),
            ], 200, ['X-Next-Page' => '']);
        });

        $client = new TestCommitApiClient('https://gitlab.example.com', 'test-token');
        $commits = $client->testFetchCommits(new ProjectId(1), new BranchName('main'));

        expect($commits)->toHaveCount(1);
        expect($attempts)->toBe(3);
    });
});
