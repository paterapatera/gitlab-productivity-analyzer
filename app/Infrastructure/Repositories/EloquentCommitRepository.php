<?php

namespace App\Infrastructure\Repositories;

use App\Application\Port\CommitRepository;
use App\Domain\Commit;
use App\Domain\ValueObjects\Additions;
use App\Domain\ValueObjects\AuthorEmail;
use App\Domain\ValueObjects\AuthorName;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\CommitMessage;
use App\Domain\ValueObjects\CommitSha;
use App\Domain\ValueObjects\CommittedDate;
use App\Domain\ValueObjects\Deletions;
use App\Infrastructure\Repositories\Eloquent\CommitEloquentModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EloquentCommitRepository implements CommitRepository
{
    /** @use ConvertsBetweenEntityAndModel<Commit, CommitEloquentModel> */
    use ConvertsBetweenEntityAndModel;

    /**
     * コミットを保存または更新
     */
    public function save(Commit $commit): Commit
    {
        return $this->saveEntity($commit);
    }

    /**
     * 複数のコミットを一括保存または更新
     *
     * @param  Collection<int, Commit>  $commits
     */
    public function saveMany(Collection $commits): void
    {
        $this->saveManyEntities($commits);
    }

    /**
     * エンティティに対応するEloquentモデルを検索
     */
    protected function findModel($entity)
    {
        return CommitEloquentModel::where('project_id', $entity->projectId->value)
            ->where('branch_name', $entity->branchName->value)
            ->where('sha', $entity->sha->value)
            ->first();
    }

    /**
     * エンティティから新しいEloquentモデルを作成
     */
    protected function createModel($entity)
    {
        $model = new CommitEloquentModel;
        $model->project_id = $entity->projectId->value;
        $model->branch_name = $entity->branchName->value;
        $model->sha = $entity->sha->value;

        return $model;
    }

    /**
     * EloquentモデルをCommitエンティティに変換
     */
    protected function toEntity($model)
    {
        /** @var CommitEloquentModel $model */
        return new Commit(
            projectId: new \App\Domain\ValueObjects\ProjectId($model->project_id),
            branchName: new BranchName($model->branch_name),
            sha: new CommitSha($model->sha),
            message: new CommitMessage($model->message ?? ''),
            committedDate: new CommittedDate($model->committed_date),
            authorName: new AuthorName($model->author_name),
            authorEmail: new AuthorEmail($model->author_email),
            additions: new Additions($model->additions),
            deletions: new Deletions($model->deletions)
        );
    }

    /**
     * CommitエンティティからEloquentモデルを更新
     */
    protected function updateModelFromEntity($model, $entity): void
    {
        /** @var CommitEloquentModel $model */
        $model->message = $entity->message->value;
        $model->committed_date = Carbon::instance($entity->committedDate->value);
        $model->author_name = $entity->authorName->value;
        $model->author_email = $entity->authorEmail->value;
        $model->additions = $entity->additions->value;
        $model->deletions = $entity->deletions->value;
    }
}
