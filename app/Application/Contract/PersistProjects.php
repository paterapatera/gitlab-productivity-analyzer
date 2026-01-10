<?php

namespace App\Application\Contract;

use App\Domain\Project;
use Illuminate\Support\Collection;

interface PersistProjects
{
    /**
     * プロジェクト情報を永続化
     *
     * @param  Collection<int, Project>  $projects
     */
    public function execute(Collection $projects): void;
}
