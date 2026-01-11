<?php

use App\Domain\Commit;
use App\Domain\Project;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\ProjectId;
use App\Infrastructure\GitLab\Exceptions\GitLabApiException;
use App\Infrastructure\GitLab\GitLabApiClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

require_once __DIR__.'/Helpers.php';

describe('GitLabApiClientクラス', function () {
    test('getProjects()メソッドでGitLab APIから全プロジェクトを取得できる', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects*' => Http::response([
                createProjectData(1, 'group/project', 'Test project', 'main'),
            ], 200, ['X-Total-Pages' => '1', 'X-Page' => '1', 'X-Per-Page' => '20']),
        ]);

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');
        $projects = $client->getProjects();

        expect($projects)->toHaveCount(1);
        expect($projects[0])->toBeInstanceOf(Project::class);
        expect($projects[0]->id->value)->toBe(1);
        expect($projects[0]->nameWithNamespace->value)->toBe('group/project');
    });

    test('fromConfig()で設定ファイルからインスタンスを作成できる', function () {
        config(['services.gitlab.base_url' => 'https://gitlab.example.com']);
        config(['services.gitlab.token' => 'env-token']);

        Http::fake([
            'gitlab.example.com/api/v4/projects*' => Http::response([], 200, ['X-Total-Pages' => '1', 'X-Page' => '1']),
        ]);

        $client = GitLabApiClient::fromConfig();
        $client->getProjects();

        Http::assertSent(function ($request) {
            return $request->hasHeader('PRIVATE-TOKEN', 'env-token');
        });
    });

    test('fromConfig()で設定が不足している場合に例外をスローする', function () {
        config(['services.gitlab.base_url' => '']);
        config(['services.gitlab.token' => '']);

        expect(fn () => GitLabApiClient::fromConfig())
            ->toThrow(\App\Infrastructure\GitLab\Exceptions\GitLabApiException::class);
    });

    test('複数ページのプロジェクトを取得できる', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects?page=1*' => Http::response([
                createProjectData(1, 'group/project1'),
                createProjectData(2, 'group/project2'),
            ], 200, ['X-Total-Pages' => '2', 'X-Page' => '1', 'X-Per-Page' => '2']),
            'gitlab.example.com/api/v4/projects?page=2*' => Http::response([
                createProjectData(3, 'group/project3'),
            ], 200, ['X-Total-Pages' => '2', 'X-Page' => '2', 'X-Per-Page' => '2']),
        ]);

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');
        $projects = $client->getProjects();

        expect($projects)->toHaveCount(3);
    });
});

describe('GitLabApiClientクラスのコミット取得機能', function () {
    test('validateBranch()でブランチが存在する場合、正常終了する', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/branches/main' => Http::response([
                'name' => 'main',
                'merged' => false,
            ], 200),
        ]);

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');
        $client->validateBranch(new ProjectId(1), new BranchName('main'));

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://gitlab.example.com/api/v4/projects/1/repository/branches/main'
                && $request->hasHeader('PRIVATE-TOKEN', 'test-token');
        });
    });

    test('validateBranch()でブランチが存在しない場合、例外をスローする', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/branches/nonexistent' => Http::response(['message' => '404 Branch Not Found'], 404),
        ]);

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');

        expect(fn () => $client->validateBranch(new ProjectId(1), new BranchName('nonexistent')))
            ->toThrow(GitLabApiException::class);
    });

    test('validateBranch()でブランチ名にスラッシュが含まれる場合、URLエンコードする', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/branches/feature%2Ftest' => Http::response([
                'name' => 'feature/test',
                'merged' => false,
            ], 200),
        ]);

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');
        $client->validateBranch(new ProjectId(1), new BranchName('feature/test'));

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'feature%2Ftest');
        });
    });

    test('getCommits()でGitLab APIからコミット一覧を取得できる', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response([
                createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2025-01-01T12:00:00Z'),
            ], 200, ['X-Next-Page' => '']),
        ]);

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');
        $commits = $client->getCommits(new ProjectId(1), new BranchName('main'));

        expect($commits)->toBeInstanceOf(\Illuminate\Support\Collection::class);
        expect($commits)->toHaveCount(1);
        expect($commits[0])->toBeInstanceOf(Commit::class);
    });

    test('getCommits()で開始日パラメータが指定された場合、sinceパラメータを送信する', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response([], 200, ['X-Next-Page' => '']),
        ]);

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');
        $sinceDate = new \DateTime('2025-01-01 12:00:00', new \DateTimeZone('UTC'));
        $client->getCommits(new ProjectId(1), new BranchName('main'), $sinceDate);

        Http::assertSent(function (Request $request) use ($sinceDate) {
            $data = $request->data();
            $expectedSince = $sinceDate->format('Y-m-d\TH:i:s\Z');

            return isset($data['since']) && $data['since'] === $expectedSince;
        });
    });

    test('getCommits()で開始日パラメータがnullの場合、sinceパラメータを送信しない', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response([], 200, ['X-Next-Page' => '']),
        ]);

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');
        $client->getCommits(new ProjectId(1), new BranchName('main'), null);

        Http::assertSent(function (Request $request) {
            $data = $request->data();

            return ! isset($data['since']);
        });
    });

    test('getCommits()で認証エラー（401）が発生した場合に例外をスローする', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response(['message' => 'Unauthorized'], 401),
        ]);

        $client = new GitLabApiClient('https://gitlab.example.com', 'invalid-token');

        expect(fn () => $client->getCommits(new ProjectId(1), new BranchName('main')))
            ->toThrow(GitLabApiException::class);
    });

    test('getCommits()で複数ページのコミットを取得できる（x-next-pageヘッダーベース）', function () {
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

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');
        $commits = $client->getCommits(new ProjectId(1), new BranchName('main'));

        expect($commits)->toHaveCount(2);
        expect($commits[0]->sha->value)->toBe('a1b2c3d4e5f6789012345678901234567890abcd');
        expect($commits[1]->sha->value)->toBe('b2c3d4e5f6789012345678901234567890abcdef');
    });

    test('getCommits()でレート制限エラー（429）が発生した場合、指数バックオフでリトライする', function () {
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

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');
        $commits = $client->getCommits(new ProjectId(1), new BranchName('main'));

        expect($commits)->toHaveCount(1);
        expect($attempts)->toBe(3);
    });

    test('getCommits()で統計情報（stats）を含むコミットを正しく取得できる', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response([
                createCommitData(
                    'a1b2c3d4e5f6789012345678901234567890abcd',
                    'Test commit',
                    '2025-01-01T12:00:00Z',
                    'John Doe',
                    'john@example.com',
                    100,
                    10
                ),
            ], 200, ['X-Next-Page' => '']),
        ]);

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');
        $commits = $client->getCommits(new ProjectId(1), new BranchName('main'));

        expect($commits)->toHaveCount(1);
        expect($commits[0]->additions->value)->toBe(100);
        expect($commits[0]->deletions->value)->toBe(10);
    });

    test('getCommits()でstatsオブジェクトが存在しない場合、デフォルト値0を使用する', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response([
                createCommitDataWithoutStats(
                    'a1b2c3d4e5f6789012345678901234567890abcd',
                    'Test commit',
                    '2025-01-01T12:00:00Z'
                ),
            ], 200, ['X-Next-Page' => '']),
        ]);

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');
        $commits = $client->getCommits(new ProjectId(1), new BranchName('main'));

        expect($commits)->toHaveCount(1);
        expect($commits[0]->additions->value)->toBe(0);
        expect($commits[0]->deletions->value)->toBe(0);
    });

    test('getCommits()で接続エラーが発生した場合に例外をスローする', function () {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
        });

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');

        expect(fn () => $client->getCommits(new ProjectId(1), new BranchName('main')))
            ->toThrow(GitLabApiException::class);
    });

    test('getCommits()でAPIエラー（500）が発生した場合に例外をスローする', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response(['message' => 'Internal Server Error'], 500),
        ]);

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');

        expect(fn () => $client->getCommits(new ProjectId(1), new BranchName('main')))
            ->toThrow(GitLabApiException::class);
    });

    test('getCommits()でwith_stats=trueパラメータを送信する', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response([], 200, ['X-Next-Page' => '']),
        ]);

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');
        $client->getCommits(new ProjectId(1), new BranchName('main'));

        Http::assertSent(function (Request $request) {
            $data = $request->data();

            return isset($data['with_stats']) && $data['with_stats'] === 'true';
        });
    });

    test('getCommits()でref_nameパラメータにブランチ名を指定する', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response([], 200, ['X-Next-Page' => '']),
        ]);

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');
        $client->getCommits(new ProjectId(1), new BranchName('main'));

        Http::assertSent(function (Request $request) {
            $data = $request->data();

            return isset($data['ref_name']) && $data['ref_name'] === 'main';
        });
    });
});
