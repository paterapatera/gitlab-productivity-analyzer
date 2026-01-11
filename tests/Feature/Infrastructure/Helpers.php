<?php

use Illuminate\Support\Facades\Http;

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
