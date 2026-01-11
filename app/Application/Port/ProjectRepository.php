<?php

namespace App\Application\Port;

use App\Domain\Project;
use App\Domain\ValueObjects\ProjectId;
use Illuminate\Support\Collection;

/**
 * プロジェクトの永続化を提供するポート
 */
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
     * @param  ProjectId  $projectId  プロジェクトID
     * @return Project|null プロジェクト（存在しない場合は null）
     */
    public function findByProjectId(ProjectId $projectId): ?Project;

    /**
     * プロジェクトを保存または更新
     *
     * @param  Project  $project  保存するプロジェクト
     * @return Project 保存されたプロジェクト
     */
    public function save(Project $project): Project;

    /**
     * 複数のプロジェクトを一括保存または更新
     *
     * @param  Collection<int, Project>  $projects  保存するプロジェクトのコレクション
     */
    public function saveMany(Collection $projects): void;

    /**
     * プロジェクトを削除
     *
     * @param  Project  $project  削除するプロジェクト
     */
    public function delete(Project $project): void;

    /**
     * プロジェクトIDのリストに存在しないプロジェクトを取得
     *
     * @param  Collection<int, ProjectId>  $projectIds  プロジェクトIDのコレクション
     * @return Collection<int, Project> 存在しないプロジェクトのコレクション
     */
    public function findNotInProjectIds(Collection $projectIds): Collection;
}
