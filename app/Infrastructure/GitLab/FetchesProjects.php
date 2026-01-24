<?php

namespace App\Infrastructure\GitLab;

use App\Domain\Project;
use App\Domain\ValueObjects\DefaultBranch;
use App\Domain\ValueObjects\ProjectDescription;
use App\Domain\ValueObjects\ProjectId;
use App\Domain\ValueObjects\ProjectNameWithNamespace;
use App\Infrastructure\GitLab\Exceptions\GitLabApiException;
use Illuminate\Support\Collection;

trait FetchesProjects
{
    use HandlesGitLabApiRequests;

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
            $response = $this->fetchProjectsPage($page);

            if (self::isResponseSuccessful($response)) {
                /** @var array<int, array<string, mixed>> $projectDataArray */
                $projectDataArray = $response->json();
                $projects = collect($projectDataArray)
                    ->map($this->convertToProject(...));
                $allProjects = $allProjects->concat($projects);

                $totalPages = (int) $response->header('X-Total-Pages') ?: 1;
                $page++;
            } elseif ($this->handleRateLimit($response, $page)) {
                // レート制限エラー: 同じページを再試行
                continue;
            } else {
                $this->checkApiError($response);
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
        return $this->makeGitLabRequest('get', '/api/v4/projects', [
            'page' => $page,
            'per_page' => 100,
        ]);
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
}
