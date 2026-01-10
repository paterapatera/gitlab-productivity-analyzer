<?php

namespace App\Application\Service;

use App\Application\Contract\GetProjects as GetProjectsInterface;
use App\Application\Port\GitApi;
use App\Domain\Project;
use App\Infrastructure\GitLab\Exceptions\GitLabApiException;
use Illuminate\Support\Collection;

class GetProjects implements GetProjectsInterface
{
    public function __construct(
        private readonly GitApi $externalClient
    ) {}

    /**
     * 外部APIから全プロジェクトを取得
     *
     * @return Collection<int, Project>
     *
     * @throws GitLabApiException
     */
    public function execute(): Collection
    {
        return $this->externalClient->getProjects();
    }
}
