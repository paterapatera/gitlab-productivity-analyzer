<?php

namespace App\Infrastructure\Repositories;

use App\Application\Port\ProjectRepository;
use App\Domain\Project;
use App\Domain\ValueObjects\DefaultBranch;
use App\Domain\ValueObjects\ProjectDescription;
use App\Domain\ValueObjects\ProjectId;
use App\Domain\ValueObjects\ProjectNameWithNamespace;
use App\Infrastructure\Repositories\Eloquent\ProjectEloquentModel;
use Illuminate\Support\Collection;

class EloquentProjectRepository implements ProjectRepository
{
    /**
     * 全プロジェクトを取得
     *
     * @return Collection<int, Project>
     */
    public function findAll(): Collection
    {
        return ProjectEloquentModel::get()
            ->map($this->toEntity(...));
    }

    /**
     * プロジェクトIDでプロジェクトを取得
     */
    public function findByProjectId(ProjectId $projectId): ?Project
    {
        $model = ProjectEloquentModel::find($projectId->value);

        return $model ? $this->toEntity($model) : null;
    }

    /**
     * プロジェクトを保存または更新
     */
    public function save(Project $project): Project
    {
        $model = ProjectEloquentModel::find($project->id->value);

        if ($model === null) {
            $model = new ProjectEloquentModel;
            $model->id = $project->id->value;
        }

        $this->updateModelFromEntity($model, $project);
        $model->save();

        return $this->toEntity($model);
    }

    /**
     * 複数のプロジェクトを一括保存または更新
     *
     * @param  Collection<int, Project>  $projects
     */
    public function saveMany(Collection $projects): void
    {
        $projects->each($this->save(...));
    }

    /**
     * プロジェクトを削除
     */
    public function delete(Project $project): void
    {
        $model = ProjectEloquentModel::find($project->id->value);
        if ($model) {
            $model->delete();
        }
    }

    /**
     * プロジェクトIDのリストに存在しないプロジェクトを取得
     *
     * @param  Collection<int, ProjectId>  $projectIds
     * @return Collection<int, Project>
     */
    public function findNotInProjectIds(Collection $projectIds): Collection
    {
        $ids = $projectIds->map(fn (ProjectId $projectId) => $projectId->value)->toArray();

        // 空のコレクションの場合は全プロジェクトを返す
        if (empty($ids)) {
            return ProjectEloquentModel::get()
                ->map($this->toEntity(...));
        }

        return ProjectEloquentModel::whereNotIn('id', $ids)
            ->get()
            ->map($this->toEntity(...));
    }

    /**
     * EloquentモデルをProjectエンティティに変換
     */
    private function toEntity(ProjectEloquentModel $model): Project
    {
        return new Project(
            id: new ProjectId($model->id),
            nameWithNamespace: new ProjectNameWithNamespace($model->name_with_namespace),
            description: new ProjectDescription($model->description),
            defaultBranch: new DefaultBranch($model->default_branch)
        );
    }

    /**
     * ProjectエンティティからEloquentモデルを更新
     */
    private function updateModelFromEntity(ProjectEloquentModel $model, Project $project): void
    {
        $model->name_with_namespace = $project->nameWithNamespace->value;
        $model->description = $project->description->value;
        $model->default_branch = $project->defaultBranch->value;
        $model->deleted_at = null;
    }
}
