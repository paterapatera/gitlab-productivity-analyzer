<?php

namespace App\Infrastructure\GitLab;

use App\Domain\Project;
use App\Domain\ValueObjects\DefaultBranch;
use App\Domain\ValueObjects\ProjectDescription;
use App\Domain\ValueObjects\ProjectId;
use App\Domain\ValueObjects\ProjectNameWithNamespace;
use App\Infrastructure\GitLab\Exceptions\GitLabApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

trait FetchesProjects
{
    /**
     * GitLab APIから全プロジェクトを取得
     *
     * @return Collection<int, Project>
     *
     * @throws GitLabApiException
     */
    protected function fetchProjects(): Collection
    {
        $allProjects = collect();
        $page = 1;
        $totalPages = 1;

        do {
            try {
                $response = $this->fetchProjectsPage($page);

                if ($response->successful()) {
                    /** @var array<int, array<string, mixed>> $projectDataArray */
                    $projectDataArray = $response->json();
                    $projects = collect($projectDataArray)
                        ->map($this->convertToProject(...));
                    $allProjects = $allProjects->concat($projects);

                    $totalPages = (int) $response->header('X-Total-Pages') ?: 1;
                    $page++;
                } elseif ($response->status() === 429) {
                    // レート制限エラー: 指数バックオフでリトライ
                    $retryAfter = (int) $response->header('Retry-After') ?: 1;
                    $delay = min($retryAfter * (2 ** ($page - 1)), 60); // 最大60秒
                    sleep($delay);

                    // 同じページを再試行
                    continue;
                } else {
                    throw new GitLabApiException(
                        "GitLab API error: {$response->status()} - {$response->body()}"
                    );
                }
            } catch (ConnectionException $e) {
                throw new GitLabApiException(
                    "GitLab API connection error: {$e->getMessage()}",
                    0,
                    $e
                );
            }
        } while ($page <= $totalPages);

        return $allProjects;
    }

    /**
     * 指定されたページのプロジェクトを取得
     *
     * @throws GitLabApiException
     */
    protected function fetchProjectsPage(int $page): \Illuminate\Http\Client\Response
    {
        $baseUrl = $this->getGitLabBaseUrl();
        $token = $this->getGitLabToken();

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders([
            'PRIVATE-TOKEN' => $token,
        ])->get("{$baseUrl}/api/v4/projects", [
            'page' => $page,
            'per_page' => 100,
        ]);

        if ($response->status() === 401) {
            throw new GitLabApiException('GitLab API authentication failed');
        }

        return $response;
    }

    /**
     * APIレスポンスをProjectエンティティに変換
     *
     * @param  array<string, mixed>  $projectData
     */
    protected function convertToProject(array $projectData): Project
    {
        return new Project(
            id: new ProjectId((int) $projectData['id']),
            nameWithNamespace: new ProjectNameWithNamespace($projectData['name_with_namespace']),
            description: new ProjectDescription($projectData['description'] ?? null),
            defaultBranch: new DefaultBranch($projectData['default_branch'] ?? null)
        );
    }

    /**
     * GitLab APIのベースURLを取得
     */
    abstract protected function getGitLabBaseUrl(): string;

    /**
     * GitLab APIの認証トークンを取得
     */
    abstract protected function getGitLabToken(): string;
}
