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
 * GitLab APIレスポンスのモックを作成するヘルパー関数
 */
function createGitLabApiResponse(array $projects, int $totalPages = 1, int $page = 1): array
{
    return [
        'gitlab.example.com/api/v4/projects*' => Http::response(
            $projects,
            200,
            ['X-Total-Pages' => (string) $totalPages, 'X-Page' => (string) $page, 'X-Per-Page' => '20']
        ),
    ];
}
