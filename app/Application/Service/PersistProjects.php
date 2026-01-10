<?php

namespace App\Application\Service;

use App\Application\Contract\PersistProjects as PersistProjectsInterface;
use App\Application\Port\ProjectRepository;
use App\Domain\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PersistProjects implements PersistProjectsInterface
{
    public function __construct(
        private readonly ProjectRepository $repository
    ) {}

    /**
     * プロジェクト情報を永続化
     *
     * @param  Collection<int, Project>  $projects
     */
    public function execute(Collection $projects): void
    {
        DB::transaction(function () use ($projects) {
            $this->repository->saveMany($projects);
        });
    }
}
