<?php

namespace App\Application\Port;

use App\Domain\Project;
use App\Infrastructure\GitLab\Exceptions\GitLabApiException;
use Illuminate\Support\Collection;

interface GitApi
{
    /**
     * 外部APIから全プロジェクトを取得
     *
     * @return Collection<int, Project>
     *
     * @throws GitLabApiException
     */
    public function getProjects(): Collection;
}
