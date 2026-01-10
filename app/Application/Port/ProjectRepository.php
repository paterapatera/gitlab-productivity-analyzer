<?php

namespace App\Application\Port;

use App\Domain\Project;
use App\Domain\ValueObjects\ProjectId;
use Illuminate\Support\Collection;

interface ProjectRepository
{
    /**
     * 全プロジェクトを取得
     *
     * @return Collection<int, Project>
     */
    public function findAll(): Collection;

    /**
     * プロジェクトIDでプロジェクトを取得
     *
     * @param  ProjectId  $projectId
     * @return Project|null
     */
    public function findByProjectId(ProjectId $projectId): ?Project;

    /**
     * プロジェクトを保存または更新
     *
     * @param  Project  $project
     * @return Project
     */
    public function save(Project $project): Project;

    /**
     * 複数のプロジェクトを一括保存または更新
     *
     * @param  Collection<int, Project>  $projects
     * @return void
     */
    public function saveMany(Collection $projects): void;

    /**
     * プロジェクトを削除
     *
     * @param  Project  $project
     * @return void
     */
    public function delete(Project $project): void;

    /**
     * プロジェクトIDのリストに存在しないプロジェクトを取得
     *
     * @param  Collection<int, ProjectId>  $projectIds
     * @return Collection<int, Project>
     */
    public function findNotInProjectIds(Collection $projectIds): Collection;
}
