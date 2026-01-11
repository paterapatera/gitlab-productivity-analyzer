<?php

use App\Application\Port\CommitCollectionHistoryRepository;
use App\Application\Port\CommitRepository;
use App\Application\Port\GitApi;
use App\Application\Port\ProjectRepository;
use App\Application\Service\CollectCommits;
use App\Domain\Project;
use App\Infrastructure\GitLab\GitLabApiClient;
use Illuminate\Support\Facades\Http;

require_once __DIR__.'/../../Helpers.php';
require_once __DIR__.'/../Infrastructure/Helpers.php';

// getCommitCollectionHistoryRepository() は tests/Helpers.php で定義済み

if (! function_exists('getCollectCommitsService')) {
    /**
     * CollectCommitsサービスのインスタンスを取得
     * 依存関係をモック可能にするため、オプションで依存関係を注入可能
     */
    function getCollectCommitsService(
        ?ProjectRepository $projectRepository = null,
        ?GitApi $gitApi = null,
        ?CommitRepository $commitRepository = null,
        ?CommitCollectionHistoryRepository $commitCollectionHistoryRepository = null
    ): CollectCommits {
        return new CollectCommits(
            $projectRepository ?? getProjectRepository(),
            $gitApi ?? app(GitApi::class),
            $commitRepository ?? getCommitRepository(),
            $commitCollectionHistoryRepository ?? getCommitCollectionHistoryRepository()
        );
    }
}

if (! function_exists('setupProjectForTest')) {
    /**
     * テスト用のプロジェクトをセットアップ
     */
    function setupProjectForTest(int $projectId = 1, string $nameWithNamespace = 'group/project1'): Project
    {
        $projectRepository = getProjectRepository();
        $project = createProject($projectId, $nameWithNamespace);
        $projectRepository->save($project);

        return $project;
    }
}

if (! function_exists('setupHttpMockForCommits')) {
    /**
     * コミット収集用のHttpモックをセットアップ
     */
    function setupHttpMockForCommits(
        int $projectId = 1,
        string $branchName = 'main',
        array $commits = [],
        bool $branchExists = true
    ): void {
        Http::fake(createCommitCollectionApiMock($projectId, $branchName, $commits, $branchExists));
    }
}

if (! function_exists('getGitLabApiClient')) {
    /**
     * GitLabApiClientのインスタンスを取得
     */
    function getGitLabApiClient(string $baseUrl = 'https://gitlab.example.com', string $token = 'test-token'): GitLabApiClient
    {
        return new GitLabApiClient($baseUrl, $token);
    }
}

// getCommitCollectionHistoryRepository() は tests/Helpers.php で定義

if (! function_exists('assertCommitsSaved')) {
    /**
     * データベースにコミットが保存されたことをアサート
     */
    function assertCommitsSaved(int $projectId, string $branchName, int $expectedCount): void
    {
        $savedCommits = \App\Infrastructure\Repositories\Eloquent\CommitEloquentModel::where('project_id', $projectId)
            ->where('branch_name', $branchName)
            ->get();
        expect($savedCommits)->toHaveCount($expectedCount);
    }
}

if (! function_exists('assertHistoryRecorded')) {
    /**
     * 収集履歴が記録されたことをアサート
     */
    function assertHistoryRecorded(int $projectId, string $branchName, ?string $expectedDate = null): void
    {
        $commitCollectionHistoryRepository = getCommitCollectionHistoryRepository();
        $historyId = new \App\Domain\ValueObjects\CommitCollectionHistoryId(
            new \App\Domain\ValueObjects\ProjectId($projectId),
            new \App\Domain\ValueObjects\BranchName($branchName)
        );
        $history = $commitCollectionHistoryRepository->findById($historyId);
        expect($history)->not->toBeNull();
        if ($expectedDate !== null) {
            expect($history->latestCommittedDate->value->format('Y-m-d H:i:s'))->toBe($expectedDate);
        }
    }
}

if (! function_exists('assertHistoryNotRecorded')) {
    /**
     * 収集履歴が記録されていないことをアサート
     */
    function assertHistoryNotRecorded(int $projectId, string $branchName): void
    {
        $commitCollectionHistoryRepository = getCommitCollectionHistoryRepository();
        $historyId = new \App\Domain\ValueObjects\CommitCollectionHistoryId(
            new \App\Domain\ValueObjects\ProjectId($projectId),
            new \App\Domain\ValueObjects\BranchName($branchName)
        );
        $history = $commitCollectionHistoryRepository->findById($historyId);
        expect($history)->toBeNull();
    }
}

if (! function_exists('assertSinceParameterSent')) {
    /**
     * GitLab APIにsinceパラメータが送信されたことをアサート
     */
    function assertSinceParameterSent(\DateTime $sinceDate): void
    {
        Http::assertSent(function (\Illuminate\Http\Client\Request $request) use ($sinceDate) {
            if (str_contains($request->url(), '/commits')) {
                $data = $request->data();
                $expectedSince = $sinceDate->format('Y-m-d\TH:i:s\Z');

                return isset($data['since']) && $data['since'] === $expectedSince;
            }

            return false;
        });
    }
}

if (! function_exists('assertSinceParameterNotSent')) {
    /**
     * GitLab APIにsinceパラメータが送信されなかったことをアサート
     */
    function assertSinceParameterNotSent(): void
    {
        Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
            if (str_contains($request->url(), '/commits')) {
                $data = $request->data();

                return ! isset($data['since']);
            }

            return false;
        });
    }
}

if (! function_exists('setupExistingCommits')) {
    /**
     * 既存のコミットをセットアップ
     *
     * @param  array<int, array<string, mixed>>  $commits  コミットデータの配列（各要素はcreateCommit()の引数）
     */
    function setupExistingCommits(int $projectId, string $branchName, array $commits): void
    {
        $commitRepository = getCommitRepository();
        $existingCommits = collect($commits)->map(function (array $commitData) use ($projectId, $branchName) {
            return createCommit(
                projectId: $projectId,
                branchName: $branchName,
                sha: $commitData['sha'] ?? 'a1b2c3d4e5f6789012345678901234567890abcd',
                message: $commitData['message'] ?? 'Initial commit',
                committedDate: $commitData['committedDate'] ?? '2024-01-01 12:00:00',
                authorName: $commitData['authorName'] ?? 'John Doe',
                authorEmail: $commitData['authorEmail'] ?? 'john.doe@example.com',
                additions: $commitData['additions'] ?? 100,
                deletions: $commitData['deletions'] ?? 50
            );
        });
        $commitRepository->saveMany($existingCommits);
    }
}

if (! function_exists('setupExistingHistory')) {
    /**
     * 既存の収集履歴をセットアップ
     */
    function setupExistingHistory(int $projectId, string $branchName, string $latestCommittedDate): void
    {
        $commitCollectionHistoryRepository = getCommitCollectionHistoryRepository();
        $history = createCommitCollectionHistory(
            projectId: $projectId,
            branchName: $branchName,
            latestCommittedDate: $latestCommittedDate
        );
        $commitCollectionHistoryRepository->save($history);
    }
}

if (! function_exists('assertCommitContent')) {
    /**
     * コミットの内容をアサート
     *
     * @param  array  $expected  期待値（message, author_name, additions, deletionsなど）
     */
    function assertCommitContent(int $projectId, string $branchName, string $sha, array $expected): void
    {
        $savedCommits = \App\Infrastructure\Repositories\Eloquent\CommitEloquentModel::where('project_id', $projectId)
            ->where('branch_name', $branchName)
            ->get();
        $commit = $savedCommits->firstWhere('sha', $sha);
        expect($commit)->not->toBeNull();
        foreach ($expected as $key => $value) {
            expect($commit->$key)->toBe($value);
        }
    }
}
