<?php

use App\Application\DTO\CollectCommitsResult;
use App\Application\Port\CommitRepository;
use App\Application\Port\GitApi;
use App\Application\Port\ProjectRepository;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\ProjectId;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

require_once __DIR__.'/../../../Helpers.php';
require_once __DIR__.'/../../Infrastructure/Helpers.php';
require_once __DIR__.'/../../../Unit/Domain/CommitTest.php';

describe('CollectCommits Serviceの統合テスト', function () {
    describe('プロジェクトとブランチの存在検証', function () {
        test('プロジェクトが存在しない場合、エラーを返す', function () {
            $projectRepository = app(ProjectRepository::class);
            $gitApi = app(GitApi::class);
            $commitRepository = app(CommitRepository::class);

            $service = new \App\Application\Service\CollectCommits(
                $projectRepository,
                $gitApi,
                $commitRepository
            );

            $result = $service->execute(
                new ProjectId(999),
                new BranchName('main')
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeTrue();
            expect($result->errorMessage)->toBe('プロジェクトが存在しません');
            expect($result->collectedCount)->toBe(0);
            expect($result->savedCount)->toBe(0);
        });

        test('ブランチが存在しない場合、エラーを返す', function () {
            $projectRepository = app(ProjectRepository::class);
            $project = createProject(1, 'group/project1');
            $projectRepository->save($project);

            Http::fake([
                'gitlab.example.com/api/v4/projects/1/repository/branches/nonexistent' => Http::response(
                    ['message' => '404 Branch Not Found'],
                    404
                ),
            ]);

            $gitApi = new \App\Infrastructure\GitLab\GitLabApiClient('https://gitlab.example.com', 'test-token');
            $commitRepository = app(CommitRepository::class);

            $service = new \App\Application\Service\CollectCommits(
                $projectRepository,
                $gitApi,
                $commitRepository
            );

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('nonexistent')
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeTrue();
            expect($result->errorMessage)->not->toBeNull();
            expect($result->errorMessage)->not->toBeEmpty();
            expect($result->collectedCount)->toBe(0);
            expect($result->savedCount)->toBe(0);
        });
    });

    describe('開始日が指定された場合のフィルタリング', function () {
        test('開始日以降のコミットのみを収集する', function () {
            $projectRepository = app(ProjectRepository::class);
            $project = createProject(1, 'group/project1');
            $projectRepository->save($project);

            Http::fake([
                'gitlab.example.com/api/v4/projects/1/repository/branches/main' => Http::response([
                    'name' => 'main',
                    'merged' => false,
                ], 200),
                'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response([
                    createCommitData(
                        'a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2025-01-15T12:00:00Z'
                    ),
                    createCommitData(
                        'b2c3d4e5f6789012345678901234567890abcdef', 'Commit 2', '2025-01-20T12:00:00Z'
                    ),
                ], 200, ['X-Next-Page' => '']),
            ]);

            $gitApi = new \App\Infrastructure\GitLab\GitLabApiClient('https://gitlab.example.com', 'test-token');
            $commitRepository = app(CommitRepository::class);

            $service = new \App\Application\Service\CollectCommits(
                $projectRepository,
                $gitApi,
                $commitRepository
            );

            $sinceDate = new \DateTime('2025-01-10 12:00:00', new \DateTimeZone('UTC'));
            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main'),
                $sinceDate
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeFalse();
            expect($result->collectedCount)->toBe(2);
            expect($result->savedCount)->toBe(2);

            // GitLab APIにsinceパラメータが送信されたことを確認
            Http::assertSent(function (Request $request) use ($sinceDate) {
                if (str_contains($request->url(), '/commits')) {
                    $data = $request->data();
                    $expectedSince = $sinceDate->format('Y-m-d\TH:i:s\Z');

                    return isset($data['since']) && $data['since'] === $expectedSince;
                }

                return false;
            });

            // データベースにコミットが保存されたことを確認
            $savedCommits = \App\Infrastructure\Repositories\Eloquent\CommitEloquentModel::where('project_id', 1)
                ->where('branch_name', 'main')
                ->get();
            expect($savedCommits)->toHaveCount(2);
        });
    });

    describe('開始日が指定されない場合の全件取得', function () {
        test('すべてのコミットを収集する', function () {
            $projectRepository = app(ProjectRepository::class);
            $project = createProject(1, 'group/project1');
            $projectRepository->save($project);

            Http::fake([
                'gitlab.example.com/api/v4/projects/1/repository/branches/main' => Http::response([
                    'name' => 'main',
                    'merged' => false,
                ], 200),
                'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response([
                    createCommitData(
                        'a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2025-01-01T12:00:00Z'
                    ),
                    createCommitData(
                        'b2c3d4e5f6789012345678901234567890abcdef', 'Commit 2', '2025-01-05T12:00:00Z'
                    ),
                    createCommitData(
                        'c3d4e5f6789012345678901234567890abcdef01', 'Commit 3', '2025-01-10T12:00:00Z'
                    ),
                ], 200, ['X-Next-Page' => '']),
            ]);

            $gitApi = new \App\Infrastructure\GitLab\GitLabApiClient('https://gitlab.example.com', 'test-token');
            $commitRepository = app(CommitRepository::class);

            $service = new \App\Application\Service\CollectCommits(
                $projectRepository,
                $gitApi,
                $commitRepository
            );

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main'),
                null
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeFalse();
            expect($result->collectedCount)->toBe(3);
            expect($result->savedCount)->toBe(3);

            // GitLab APIにsinceパラメータが送信されなかったことを確認
            Http::assertSent(function (Request $request) {
                if (str_contains($request->url(), '/commits')) {
                    $data = $request->data();

                    return ! isset($data['since']);
                }

                return false;
            });

            // データベースにコミットが保存されたことを確認
            $savedCommits = \App\Infrastructure\Repositories\Eloquent\CommitEloquentModel::where('project_id', 1)
                ->where('branch_name', 'main')
                ->get();
            expect($savedCommits)->toHaveCount(3);
        });
    });

    describe('コミット取得と永続化のフロー', function () {
        test('コミットを取得してデータベースに保存する', function () {
            $projectRepository = app(ProjectRepository::class);
            $project = createProject(1, 'group/project1');
            $projectRepository->save($project);

            Http::fake([
                'gitlab.example.com/api/v4/projects/1/repository/branches/main' => Http::response([
                    'name' => 'main',
                    'merged' => false,
                ], 200),
                'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response([
                    createCommitData(
                        'a1b2c3d4e5f6789012345678901234567890abcd',
                        'Initial commit',
                        '2025-01-01T12:00:00Z',
                        'John Doe',
                        'john@example.com',
                        100,
                        10
                    ),
                    createCommitData(
                        'b2c3d4e5f6789012345678901234567890abcdef',
                        'Second commit',
                        '2025-01-02T12:00:00Z',
                        'Jane Doe',
                        'jane@example.com',
                        200,
                        20
                    ),
                ], 200, ['X-Next-Page' => '']),
            ]);

            $gitApi = new \App\Infrastructure\GitLab\GitLabApiClient('https://gitlab.example.com', 'test-token');
            $commitRepository = app(CommitRepository::class);

            $service = new \App\Application\Service\CollectCommits(
                $projectRepository,
                $gitApi,
                $commitRepository
            );

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main')
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeFalse();
            expect($result->collectedCount)->toBe(2);
            expect($result->savedCount)->toBe(2);

            // データベースにコミットが保存されたことを確認
            $savedCommits = \App\Infrastructure\Repositories\Eloquent\CommitEloquentModel::where('project_id', 1)
                ->where('branch_name', 'main')
                ->get();
            expect($savedCommits)->toHaveCount(2);

            // コミットの内容を確認
            $commit1 = $savedCommits->firstWhere('sha', 'a1b2c3d4e5f6789012345678901234567890abcd');
            expect($commit1)->not->toBeNull();
            expect($commit1->message)->toBe('Initial commit');
            expect($commit1->author_name)->toBe('John Doe');
            expect($commit1->additions)->toBe(100);
            expect($commit1->deletions)->toBe(10);

            $commit2 = $savedCommits->firstWhere('sha', 'b2c3d4e5f6789012345678901234567890abcdef');
            expect($commit2)->not->toBeNull();
            expect($commit2->message)->toBe('Second commit');
            expect($commit2->author_name)->toBe('Jane Doe');
            expect($commit2->additions)->toBe(200);
            expect($commit2->deletions)->toBe(20);
        });

        test('既存のコミットを更新する', function () {
            $projectRepository = app(ProjectRepository::class);
            $project = createProject(1, 'group/project1');
            $projectRepository->save($project);

            // 既存のコミットを作成
            $commitRepository = app(CommitRepository::class);
            $existingCommit = createCommit(
                projectId: 1,
                branchName: 'main',
                sha: 'a1b2c3d4e5f6789012345678901234567890abcd',
                message: 'Old message',
                committedDate: '2025-01-01 12:00:00',
                authorName: 'John Doe',
                authorEmail: 'john@example.com',
                additions: 50,
                deletions: 5
            );
            $commitRepository->save($existingCommit);

            Http::fake([
                'gitlab.example.com/api/v4/projects/1/repository/branches/main' => Http::response([
                    'name' => 'main',
                    'merged' => false,
                ], 200),
                'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response([
                    createCommitData(
                        'a1b2c3d4e5f6789012345678901234567890abcd',
                        'Updated message',
                        '2025-01-01T12:00:00Z',
                        'John Doe',
                        'john@example.com',
                        100,
                        10
                    ),
                ], 200, ['X-Next-Page' => '']),
            ]);

            $gitApi = new \App\Infrastructure\GitLab\GitLabApiClient('https://gitlab.example.com', 'test-token');

            $service = new \App\Application\Service\CollectCommits(
                $projectRepository,
                $gitApi,
                $commitRepository
            );

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main')
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeFalse();
            expect($result->collectedCount)->toBe(1);
            expect($result->savedCount)->toBe(1);

            // データベースにコミットが1件のみ存在することを確認（更新された）
            $savedCommits = \App\Infrastructure\Repositories\Eloquent\CommitEloquentModel::where('project_id', 1)
                ->where('branch_name', 'main')
                ->get();
            expect($savedCommits)->toHaveCount(1);

            // コミットが更新されたことを確認
            $updatedCommit = $savedCommits->first();
            expect($updatedCommit->message)->toBe('Updated message');
            expect($updatedCommit->additions)->toBe(100);
            expect($updatedCommit->deletions)->toBe(10);
        });
    });

    describe('エラーハンドリング', function () {
        test('GitLab API接続エラー時にエラーを返す', function () {
            $projectRepository = app(ProjectRepository::class);
            $project = createProject(1, 'group/project1');
            $projectRepository->save($project);

            Http::fake([
                'gitlab.example.com/api/v4/projects/1/repository/branches/main' => Http::response([
                    'name' => 'main',
                    'merged' => false,
                ], 200),
                'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response(
                    ['message' => 'Internal Server Error'],
                    500
                ),
            ]);

            $gitApi = new \App\Infrastructure\GitLab\GitLabApiClient('https://gitlab.example.com', 'test-token');
            $commitRepository = app(CommitRepository::class);

            $service = new \App\Application\Service\CollectCommits(
                $projectRepository,
                $gitApi,
                $commitRepository
            );

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main')
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeTrue();
            expect($result->errorMessage)->not->toBeNull();
            expect($result->collectedCount)->toBe(0);
            expect($result->savedCount)->toBe(0);
        });

        test('認証エラー時にエラーを返す', function () {
            $projectRepository = app(ProjectRepository::class);
            $project = createProject(1, 'group/project1');
            $projectRepository->save($project);

            Http::fake([
                'gitlab.example.com/api/v4/projects/1/repository/branches/main' => Http::response([
                    'name' => 'main',
                    'merged' => false,
                ], 200),
                'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response(
                    ['message' => 'Unauthorized'],
                    401
                ),
            ]);

            $gitApi = new \App\Infrastructure\GitLab\GitLabApiClient('https://gitlab.example.com', 'invalid-token');
            $commitRepository = app(CommitRepository::class);

            $service = new \App\Application\Service\CollectCommits(
                $projectRepository,
                $gitApi,
                $commitRepository
            );

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main')
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeTrue();
            expect($result->errorMessage)->not->toBeNull();
            expect($result->collectedCount)->toBe(0);
            expect($result->savedCount)->toBe(0);
        });
    });
});
