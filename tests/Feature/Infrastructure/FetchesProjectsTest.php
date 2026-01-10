<?php

use App\Domain\Project;
use App\Infrastructure\GitLab\GitLabApiClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

require_once __DIR__.'/Helpers.php';

describe('FetchesProjectsトレイトの機能', function () {
    test('GitLab APIからプロジェクト一覧を取得できる', function () {
        Http::fake(createGitLabApiResponse([
            createProjectData(1, 'group/project', 'Test project', 'main'),
        ]));

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');
        $projects = $client->getProjects();

        expect($projects)->toBeInstanceOf(\Illuminate\Support\Collection::class);
        expect($projects)->toHaveCount(1);
        expect($projects[0])->toBeInstanceOf(Project::class);
        expect($projects[0]->id->value)->toBe(1);
        expect($projects[0]->nameWithNamespace->value)->toBe('group/project');
        expect($projects[0]->description->value)->toBe('Test project');
        expect($projects[0]->defaultBranch->value)->toBe('main');
    });

    test('PRIVATE-TOKENヘッダーで認証する', function () {
        Http::fake(createGitLabApiResponse([]));

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');
        $client->getProjects();

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('PRIVATE-TOKEN', 'test-token');
        });
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

    test('nullのdescriptionとdefault_branchを正しく処理する', function () {
        Http::fake(createGitLabApiResponse([
            createProjectData(1, 'group/project', null, null),
        ]));

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');
        $projects = $client->getProjects();

        expect($projects[0]->description->value)->toBeNull();
        expect($projects[0]->defaultBranch->value)->toBeNull();
    });
});

describe('エラーハンドリング', function () {
    test('認証エラー（401）が発生した場合に例外をスローする', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects*' => Http::response(['message' => 'Unauthorized'], 401),
        ]);

        $client = new GitLabApiClient('https://gitlab.example.com', 'invalid-token');

        expect(fn () => $client->getProjects())
            ->toThrow(\App\Infrastructure\GitLab\Exceptions\GitLabApiException::class);
    });

    test('タイムアウトエラーが発生した場合に例外をスローする', function () {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
        });

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');

        expect(fn () => $client->getProjects())
            ->toThrow(\App\Infrastructure\GitLab\Exceptions\GitLabApiException::class);
    });

    test('APIエラー（500）が発生した場合に例外をスローする', function () {
        Http::fake([
            'gitlab.example.com/api/v4/projects*' => Http::response(['message' => 'Internal Server Error'], 500),
        ]);

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');

        expect(fn () => $client->getProjects())
            ->toThrow(\App\Infrastructure\GitLab\Exceptions\GitLabApiException::class);
    });

    test('429エラー時に指数バックオフでリトライする', function () {
        $attempts = 0;

        Http::fake(function () use (&$attempts) {
            $attempts++;
            if ($attempts < 3) {
                return Http::response(['message' => 'Too Many Requests'], 429, ['Retry-After' => '1']);
            }

            return Http::response([
                createProjectData(1, 'group/project'),
            ], 200, ['X-Total-Pages' => '1', 'X-Page' => '1', 'X-Per-Page' => '20']);
        });

        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');
        $projects = $client->getProjects();

        expect($projects)->toHaveCount(1);
        expect($attempts)->toBe(3);
    });
});
