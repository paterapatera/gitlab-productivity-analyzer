<?php

namespace App\Application\Contract;

use App\Domain\Project;
use App\Infrastructure\GitLab\Exceptions\GitLabApiException;
use Illuminate\Support\Collection;

interface GetProjects
{
    /**
     * 外部APIから全プロジェクトを取得
     *
     * @return Collection<int, Project>
     *
     * @throws GitLabApiException
     */
    public function execute(): Collection;
}
