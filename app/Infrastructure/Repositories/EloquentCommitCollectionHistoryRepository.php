<?php

namespace App\Infrastructure\Repositories;

use App\Application\Port\CommitCollectionHistoryRepository;
use App\Domain\CommitCollectionHistory;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\CommitCollectionHistoryId;
use App\Domain\ValueObjects\CommittedDate;
use App\Domain\ValueObjects\ProjectId;
use App\Infrastructure\Repositories\Eloquent\CommitCollectionHistoryEloquentModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EloquentCommitCollectionHistoryRepository implements CommitCollectionHistoryRepository
{
    /** @use ConvertsBetweenEntityAndModel<CommitCollectionHistory, CommitCollectionHistoryEloquentModel> */
    use ConvertsBetweenEntityAndModel;

    /**
     * 収集履歴を保存または更新
     */
    public function save(CommitCollectionHistory $history): CommitCollectionHistory
    {
        return $this->saveEntity($history);
    }

    /**
     * プロジェクトIDとブランチ名で収集履歴を取得
     */
    public function findById(CommitCollectionHistoryId $id): ?CommitCollectionHistory
    {
        $model = CommitCollectionHistoryEloquentModel::where('project_id', $id->projectId->value)
            ->where('branch_name', $id->branchName->value)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    /**
     * すべての収集履歴を取得
     *
     * @return Collection<int, CommitCollectionHistory>
     */
    public function findAll(): Collection
    {
        return CommitCollectionHistoryEloquentModel::get()
            ->map($this->toEntity(...));
    }

    /**
     * エンティティに対応するEloquentモデルを検索
     */
    protected function findModel($entity)
    {
        return CommitCollectionHistoryEloquentModel::where('project_id', $entity->id->projectId->value)
            ->where('branch_name', $entity->id->branchName->value)
            ->first();
    }

    /**
     * エンティティから新しいEloquentモデルを作成
     */
    protected function createModel($entity)
    {
        $model = new CommitCollectionHistoryEloquentModel;
        $model->project_id = $entity->id->projectId->value;
        $model->branch_name = $entity->id->branchName->value;

        return $model;
    }

    /**
     * EloquentモデルをCommitCollectionHistoryエンティティに変換
     */
    protected function toEntity($model)
    {
        /** @var CommitCollectionHistoryEloquentModel $model */
        return new CommitCollectionHistory(
            id: new CommitCollectionHistoryId(
                projectId: new ProjectId($model->project_id),
                branchName: new BranchName($model->branch_name)
            ),
            latestCommittedDate: new CommittedDate($model->latest_committed_date)
        );
    }

    /**
     * CommitCollectionHistoryエンティティからEloquentモデルを更新
     */
    protected function updateModelFromEntity($model, $entity): void
    {
        /** @var CommitCollectionHistoryEloquentModel $model */
        $model->latest_committed_date = Carbon::instance($entity->latestCommittedDate->value);
    }
}
