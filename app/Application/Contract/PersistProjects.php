<?php

namespace App\Application\Contract;

use App\Domain\Project;
use Illuminate\Support\Collection;

/**
 * プロジェクト情報を永続化する契約
 */
interface PersistProjects
{
    /**
     * プロジェクト情報を永続化
     *
     * @param  Collection<int, Project>  $projects
     */
    public function execute(Collection $projects): void;
}
