<?php

use App\Application\DTO\CollectCommitsResult;
use App\Application\Port\CommitRepository;
use App\Application\Port\GitApi;
use App\Application\Port\ProjectRepository;
use App\Application\Service\CollectCommits as CollectCommitsService;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\ProjectId;
use App\Infrastructure\GitLab\Exceptions\GitLabApiException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

require_once __DIR__.'/../../Domain/CommitTest.php';

describe('CollectCommitsの機能', function () {
    beforeEach(function () {
        // DBファサードをモック
        DB::shouldReceive('transaction')
            ->andReturnUsing(function ($callback) {
                return $callback();
            });
    });

    test('プロジェクトとブランチが存在する場合、コミットを収集・永続化できる', function () {
        $projectId = new ProjectId(1);
        $branchName = new BranchName('main');
        $commits = collect([
            createCommit(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2024-01-01 12:00:00'),
            createCommit(1, 'main', 'b2c3d4e5f6789012345678901234567890abcde1', 'Second commit', '2024-01-02 12:00:00'),
        ]);

        $mockProjectRepository = Mockery::mock(ProjectRepository::class);
        $mockProjectRepository->shouldReceive('findByProjectId')
            ->once()
            ->with($projectId)
            ->andReturn(createProject(1, 'group/project'));

        $mockGitApi = Mockery::mock(GitApi::class);
        $mockGitApi->shouldReceive('validateBranch')
            ->once()
            ->with($projectId, $branchName);
        $mockGitApi->shouldReceive('getCommits')
            ->once()
            ->with($projectId, $branchName, null)
            ->andReturn($commits);

        $mockCommitRepository = Mockery::mock(CommitRepository::class);
        $mockCommitRepository->shouldReceive('saveMany')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg instanceof Collection && $arg->count() === 2;
            }));

        $service = new CollectCommitsService(
            $mockProjectRepository,
            $mockGitApi,
            $mockCommitRepository
        );

        $result = $service->execute($projectId, $branchName);

        expect($result)->toBeInstanceOf(CollectCommitsResult::class);
        expect($result->collectedCount)->toBe(2);
        expect($result->savedCount)->toBe(2);
        expect($result->hasErrors)->toBeFalse();
    });

    test('プロジェクトが存在しない場合、エラーを返す', function () {
        $projectId = new ProjectId(999);
        $branchName = new BranchName('main');

        $mockProjectRepository = Mockery::mock(ProjectRepository::class);
        $mockProjectRepository->shouldReceive('findByProjectId')
            ->once()
            ->with($projectId)
            ->andReturn(null);

        $mockGitApi = Mockery::mock(GitApi::class);
        $mockCommitRepository = Mockery::mock(CommitRepository::class);

        $service = new CollectCommitsService(
            $mockProjectRepository,
            $mockGitApi,
            $mockCommitRepository
        );

        $result = $service->execute($projectId, $branchName);

        expect($result)->toBeInstanceOf(CollectCommitsResult::class);
        expect($result->collectedCount)->toBe(0);
        expect($result->savedCount)->toBe(0);
        expect($result->hasErrors)->toBeTrue();
        expect($result->errorMessage)->toContain('プロジェクトが存在しません');
    });

    test('ブランチが存在しない場合、エラーを返す', function () {
        $projectId = new ProjectId(1);
        $branchName = new BranchName('nonexistent');

        $mockProjectRepository = Mockery::mock(ProjectRepository::class);
        $mockProjectRepository->shouldReceive('findByProjectId')
            ->once()
            ->with($projectId)
            ->andReturn(createProject(1, 'group/project'));

        $mockGitApi = Mockery::mock(GitApi::class);
        $mockGitApi->shouldReceive('validateBranch')
            ->once()
            ->with($projectId, $branchName)
            ->andThrow(new GitLabApiException('Branch not found'));

        $mockCommitRepository = Mockery::mock(CommitRepository::class);

        $service = new CollectCommitsService(
            $mockProjectRepository,
            $mockGitApi,
            $mockCommitRepository
        );

        $result = $service->execute($projectId, $branchName);

        expect($result)->toBeInstanceOf(CollectCommitsResult::class);
        expect($result->collectedCount)->toBe(0);
        expect($result->savedCount)->toBe(0);
        expect($result->hasErrors)->toBeTrue();
        expect($result->errorMessage)->toContain('Branch not found');
    });

    test('開始日が指定された場合、その日以降のコミットのみを収集する', function () {
        $projectId = new ProjectId(1);
        $branchName = new BranchName('main');
        $sinceDate = new \DateTime('2024-01-02 00:00:00');
        $commits = collect([
            createCommit(1, 'main', 'b2c3d4e5f6789012345678901234567890abcde1', 'Second commit', '2024-01-02 12:00:00'),
        ]);

        $mockProjectRepository = Mockery::mock(ProjectRepository::class);
        $mockProjectRepository->shouldReceive('findByProjectId')
            ->once()
            ->with($projectId)
            ->andReturn(createProject(1, 'group/project'));

        $mockGitApi = Mockery::mock(GitApi::class);
        $mockGitApi->shouldReceive('validateBranch')
            ->once()
            ->with($projectId, $branchName);
        $mockGitApi->shouldReceive('getCommits')
            ->once()
            ->with($projectId, $branchName, $sinceDate)
            ->andReturn($commits);

        $mockCommitRepository = Mockery::mock(CommitRepository::class);
        $mockCommitRepository->shouldReceive('saveMany')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg instanceof Collection && $arg->count() === 1;
            }));

        $service = new CollectCommitsService(
            $mockProjectRepository,
            $mockGitApi,
            $mockCommitRepository
        );

        $result = $service->execute($projectId, $branchName, $sinceDate);

        expect($result)->toBeInstanceOf(CollectCommitsResult::class);
        expect($result->collectedCount)->toBe(1);
        expect($result->savedCount)->toBe(1);
        expect($result->hasErrors)->toBeFalse();
    });

    test('開始日が指定されない場合、すべてのコミットを収集する', function () {
        $projectId = new ProjectId(1);
        $branchName = new BranchName('main');
        $commits = collect([
            createCommit(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2024-01-01 12:00:00'),
            createCommit(1, 'main', 'b2c3d4e5f6789012345678901234567890abcde1', 'Second commit', '2024-01-02 12:00:00'),
        ]);

        $mockProjectRepository = Mockery::mock(ProjectRepository::class);
        $mockProjectRepository->shouldReceive('findByProjectId')
            ->once()
            ->with($projectId)
            ->andReturn(createProject(1, 'group/project'));

        $mockGitApi = Mockery::mock(GitApi::class);
        $mockGitApi->shouldReceive('validateBranch')
            ->once()
            ->with($projectId, $branchName);
        $mockGitApi->shouldReceive('getCommits')
            ->once()
            ->with($projectId, $branchName, null)
            ->andReturn($commits);

        $mockCommitRepository = Mockery::mock(CommitRepository::class);
        $mockCommitRepository->shouldReceive('saveMany')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg instanceof Collection && $arg->count() === 2;
            }));

        $service = new CollectCommitsService(
            $mockProjectRepository,
            $mockGitApi,
            $mockCommitRepository
        );

        $result = $service->execute($projectId, $branchName);

        expect($result)->toBeInstanceOf(CollectCommitsResult::class);
        expect($result->collectedCount)->toBe(2);
        expect($result->savedCount)->toBe(2);
        expect($result->hasErrors)->toBeFalse();
    });

    test('コミット取得中にエラーが発生した場合、エラー情報を返す', function () {
        $projectId = new ProjectId(1);
        $branchName = new BranchName('main');

        $mockProjectRepository = Mockery::mock(ProjectRepository::class);
        $mockProjectRepository->shouldReceive('findByProjectId')
            ->once()
            ->with($projectId)
            ->andReturn(createProject(1, 'group/project'));

        $mockGitApi = Mockery::mock(GitApi::class);
        $mockGitApi->shouldReceive('validateBranch')
            ->once()
            ->with($projectId, $branchName);
        $mockGitApi->shouldReceive('getCommits')
            ->once()
            ->with($projectId, $branchName, null)
            ->andThrow(new GitLabApiException('API connection error'));

        $mockCommitRepository = Mockery::mock(CommitRepository::class);

        $service = new CollectCommitsService(
            $mockProjectRepository,
            $mockGitApi,
            $mockCommitRepository
        );

        $result = $service->execute($projectId, $branchName);

        expect($result)->toBeInstanceOf(CollectCommitsResult::class);
        expect($result->collectedCount)->toBe(0);
        expect($result->savedCount)->toBe(0);
        expect($result->hasErrors)->toBeTrue();
        expect($result->errorMessage)->toBe('API connection error');
    });

    test('コミット保存中にエラーが発生した場合、エラー情報を返す', function () {
        $projectId = new ProjectId(1);
        $branchName = new BranchName('main');
        $commits = collect([
            createCommit(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2024-01-01 12:00:00'),
        ]);

        $mockProjectRepository = Mockery::mock(ProjectRepository::class);
        $mockProjectRepository->shouldReceive('findByProjectId')
            ->once()
            ->with($projectId)
            ->andReturn(createProject(1, 'group/project'));

        $mockGitApi = Mockery::mock(GitApi::class);
        $mockGitApi->shouldReceive('validateBranch')
            ->once()
            ->with($projectId, $branchName);
        $mockGitApi->shouldReceive('getCommits')
            ->once()
            ->with($projectId, $branchName, null)
            ->andReturn($commits);

        $mockCommitRepository = Mockery::mock(CommitRepository::class);
        $mockCommitRepository->shouldReceive('saveMany')
            ->once()
            ->andThrow(new \Exception('Database error'));

        $service = new CollectCommitsService(
            $mockProjectRepository,
            $mockGitApi,
            $mockCommitRepository
        );

        $result = $service->execute($projectId, $branchName);

        expect($result)->toBeInstanceOf(CollectCommitsResult::class);
        expect($result->collectedCount)->toBe(1);
        expect($result->savedCount)->toBe(0);
        expect($result->hasErrors)->toBeTrue();
        expect($result->errorMessage)->toBe('Database error');
    });
});
