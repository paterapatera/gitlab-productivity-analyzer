<?php

use App\Domain\Project;
use App\Infrastructure\GitLab\GitLabApiClient;
use Illuminate\Support\Facades\Http;

require_once __DIR__.'/Helpers.php';

describe('GitLabApiClientクラス', function () {
    test('GitApiインターフェースを実装している', function () {
        $client = new GitLabApiClient('https://gitlab.example.com', 'test-token');

        expect($client)->toBeInstanceOf(\App\Application\Port\GitApi::class);
    });

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
