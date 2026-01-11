<?php

namespace App\Infrastructure\GitLab;

use App\Application\Port\GitApi;
use App\Domain\Commit;
use App\Domain\Project;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\ProjectId;
use App\Infrastructure\GitLab\Exceptions\GitLabApiException;
use Illuminate\Support\Collection;

class GitLabApiClient implements GitApi
{
    use FetchesBranches;
    use FetchesCommits;
    use FetchesProjects;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $token
    ) {}

    /**
     * 設定ファイルからGitLabApiClientインスタンスを作成
     */
    public static function fromConfig(): self
    {
        $baseUrl = config('services.gitlab.base_url');
        $token = config('services.gitlab.token');

        if (empty($baseUrl) || empty($token)) {
            throw new GitLabApiException('GitLab API configuration is missing. Please set GITLAB_BASE_URL and GITLAB_TOKEN in your .env file.');
        }

        return new self($baseUrl, $token);
    }

    /**
     * GitLab APIから全プロジェクトを取得
     *
     * @return Collection<int, Project>
     *
     * @throws GitLabApiException
     */
    public function getProjects(): Collection
    {
        return $this->fetchProjects();
    }

    protected function getGitLabBaseUrl(): string
    {
        return $this->baseUrl;
    }

    protected function getGitLabToken(): string
    {
        return $this->token;
    }

    /**
     * ブランチの存在を検証
     *
     * @param  ProjectId  $projectId  プロジェクトID
     * @param  BranchName  $branchName  ブランチ名
     *
     * @throws \App\Infrastructure\GitLab\Exceptions\GitLabApiException ブランチが存在しない場合、またはAPIエラー
     */
    public function validateBranch(ProjectId $projectId, BranchName $branchName): void
    {
        $this->fetchBranchValidation($projectId, $branchName);
    }

    /**
     * プロジェクトとブランチを指定してコミットを取得
     *
     * @param  ProjectId  $projectId  プロジェクトID
     * @param  BranchName  $branchName  ブランチ名
     * @param  \DateTime|null  $sinceDate  開始日（オプショナル、指定された場合はこの日以降のコミットのみを取得）
     * @return Collection<int, Commit> コミットのコレクション
     *
     * @throws GitLabApiException APIエラー
     */
    public function getCommits(ProjectId $projectId, BranchName $branchName, ?\DateTime $sinceDate = null): Collection
    {
        return $this->fetchCommits($projectId, $branchName, $sinceDate);
    }
}
