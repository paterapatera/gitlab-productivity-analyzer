<?php

use App\Domain\Project;
use App\Infrastructure\Repositories\EloquentCommitCollectionHistoryRepository;
use App\Infrastructure\Repositories\EloquentCommitRepository;
use App\Infrastructure\Repositories\EloquentProjectRepository;
use Illuminate\Support\Facades\Http;

require_once __DIR__.'/../../Helpers.php';

/**
 * プロジェクトデータを作成するヘルパー関数
 */
function createProjectData(int $id, string $nameWithNamespace, ?string $description = null, ?string $defaultBranch = null): array
{
    return [
        'id' => $id,
        'name_with_namespace' => $nameWithNamespace,
        'description' => $description,
        'default_branch' => $defaultBranch,
    ];
}

/**
 * GitLab API レスポンスのモックを作成するヘルパー関数
 */
function createGitLabApiResponse(
    array $projects,
    int $totalPages = 1,
    int $page = 1
): array {
    return [
        'gitlab.example.com/api/v4/projects*' => Http::response(
            $projects,
            200,
            [
                'X-Total-Pages' => (string) $totalPages,
                'X-Page' => (string) $page,
                'X-Per-Page' => '20',
            ]
        ),
    ];
}

/**
 * コミットデータを作成するヘルパー関数
 */
function createCommitData(
    string $sha,
    string $message,
    string $committedDate,
    ?string $authorName = 'John Doe',
    ?string $authorEmail = 'john@example.com',
    int $additions = 0,
    int $deletions = 0
): array {
    return [
        'id' => $sha,
        'short_id' => substr($sha, 0, 7),
        'title' => $message,
        'message' => $message,
        'author_name' => $authorName,
        'author_email' => $authorEmail,
        'committed_date' => $committedDate,
        'created_at' => $committedDate,
        'stats' => [
            'additions' => $additions,
            'deletions' => $deletions,
            'total' => $additions + $deletions,
        ],
    ];
}

/**
 * stats オブジェクトなしのコミットデータを作成するヘルパー関数
 */
function createCommitDataWithoutStats(
    string $sha,
    string $message,
    string $committedDate,
    ?string $authorName = 'John Doe',
    ?string $authorEmail = 'john@example.com'
): array {
    return [
        'id' => $sha,
        'short_id' => substr($sha, 0, 7),
        'title' => $message,
        'message' => $message,
        'author_name' => $authorName,
        'author_email' => $authorEmail,
        'committed_date' => $committedDate,
        'created_at' => $committedDate,
    ];
}

/**
 * コミット収集用の GitLab API モックを作成するヘルパー関数
 */
function createCommitCollectionApiMock(
    int $projectId,
    string $branchName,
    array $commits,
    bool $branchExists = true
): array {
    $mocks = [];

    if ($branchExists) {
        $mocks["gitlab.example.com/api/v4/projects/{$projectId}/repository/branches/{$branchName}"] = Http::response(
            [
                'name' => $branchName,
                'merged' => false,
            ],
            200
        );

        $mocks["gitlab.example.com/api/v4/projects/{$projectId}/repository/commits*"] = Http::response(
            $commits,
            200,
            ['X-Next-Page' => '']
        );
    } else {
        $mocks["gitlab.example.com/api/v4/projects/{$projectId}/repository/branches/{$branchName}"] = Http::response(
            ['message' => '404 Branch Not Found'],
            404
        );
    }

    return $mocks;
}

/**
 * テスト用のCommitCollectionHistoryエンティティを作成
 */
if (! function_exists('createCommitCollectionHistory')) {
    function createCommitCollectionHistory(
        int $projectId = 123,
        string $branchName = 'main',
        string $latestCommittedDate = '2024-01-01 12:00:00'
    ): \App\Domain\CommitCollectionHistory {
        return new \App\Domain\CommitCollectionHistory(
            id: new \App\Domain\ValueObjects\CommitCollectionHistoryId(
                projectId: new \App\Domain\ValueObjects\ProjectId($projectId),
                branchName: new \App\Domain\ValueObjects\BranchName($branchName)
            ),
            latestCommittedDate: new \App\Domain\ValueObjects\CommittedDate(new \DateTime($latestCommittedDate))
        );
    }
}

if (! function_exists('getEloquentCommitRepository')) {
    /**
     * EloquentCommitRepositoryのインスタンスを取得
     */
    function getEloquentCommitRepository(): EloquentCommitRepository
    {
        return new EloquentCommitRepository;
    }
}

if (! function_exists('getEloquentProjectRepository')) {
    /**
     * EloquentProjectRepositoryのインスタンスを取得
     */
    function getEloquentProjectRepository(): EloquentProjectRepository
    {
        return new EloquentProjectRepository;
    }
}

if (! function_exists('getEloquentCommitCollectionHistoryRepository')) {
    /**
     * EloquentCommitCollectionHistoryRepositoryのインスタンスを取得
     */
    function getEloquentCommitCollectionHistoryRepository(): EloquentCommitCollectionHistoryRepository
    {
        return new EloquentCommitCollectionHistoryRepository;
    }
}

if (! function_exists('setupProjectForRepositoryTest')) {
    /**
     * リポジトリテスト用のプロジェクトをセットアップ
     * 外部キー制約のため、プロジェクトを先に作成する必要がある場合に使用
     */
    function setupProjectForRepositoryTest(int $projectId = 1, string $nameWithNamespace = 'group/project1'): Project
    {
        $projectRepository = getProjectRepository();
        $project = createProject($projectId, $nameWithNamespace);
        $projectRepository->save($project);

        return $project;
    }
}
