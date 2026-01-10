<?php

namespace App\Application\Service;

use App\Application\Contract\GetProjects;
use App\Application\Contract\PersistProjects;
use App\Application\Contract\SyncProjects as SyncProjectsInterface;
use App\Application\DTO\SyncResult;
use App\Application\Port\ProjectRepository;
use App\Domain\Project;

class SyncProjects implements SyncProjectsInterface
{
    public function __construct(
        private readonly GetProjects $getProjects,
        private readonly PersistProjects $persistProjects,
        private readonly ProjectRepository $repository
    ) {}

    /**
     * プロジェクト情報を同期
     */
    public function execute(): SyncResult
    {
        try {
            // 外部APIからプロジェクトを取得
            $projects = $this->getProjects->execute();

            // プロジェクトを永続化
            $this->persistProjects->execute($projects);

            // 削除されたプロジェクトを検出
            $projectIds = $projects->map(fn (Project $project) => $project->id);
            $deletedProjects = $this->repository->findNotInProjectIds($projectIds);

            // 削除されたプロジェクトにdeleted_atを設定（ソフトデリート）
            $deletedCount = $deletedProjects->count();
            $deletedProjects->each(fn (Project $project) => $this->repository->delete($project));

            return new SyncResult(
                syncedCount: $projects->count(),
                deletedCount: $deletedCount,
                hasErrors: false
            );
        } catch (\Exception $e) {
            return new SyncResult(
                syncedCount: 0,
                deletedCount: 0,
                hasErrors: true,
                errorMessage: $e->getMessage()
            );
        }
    }
}
