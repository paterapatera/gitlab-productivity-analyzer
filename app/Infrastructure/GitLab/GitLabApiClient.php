<?php

namespace App\Infrastructure\GitLab;

use App\Application\Port\GitApi;
use App\Domain\Project;
use App\Infrastructure\GitLab\Exceptions\GitLabApiException;
use Illuminate\Support\Collection;

class GitLabApiClient implements GitApi
{
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
}
