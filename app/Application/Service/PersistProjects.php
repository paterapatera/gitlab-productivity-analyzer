<?php

namespace App\Application\Service;

use App\Application\Contract\PersistProjects as PersistProjectsInterface;
use App\Application\Port\ProjectRepository;
use App\Domain\Project;
use Illuminate\Support\Collection;

/**
 * プロジェクト情報を永続化するサービス
 */
class PersistProjects extends BaseService implements PersistProjectsInterface
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
        $this->transaction(function () use ($projects) {
            $this->repository->saveMany($projects);
        });
    }
}
