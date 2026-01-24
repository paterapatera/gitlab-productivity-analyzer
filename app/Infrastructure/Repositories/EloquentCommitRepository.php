<?php

namespace App\Infrastructure\Repositories;

use App\Application\Port\CommitRepository;
use App\Domain\Commit;
use App\Domain\ValueObjects\Additions;
use App\Domain\ValueObjects\AuthorEmail;
use App\Domain\ValueObjects\AuthorName;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\CommitId;
use App\Domain\ValueObjects\CommitMessage;
use App\Domain\ValueObjects\CommitSha;
use App\Domain\ValueObjects\CommittedDate;
use App\Domain\ValueObjects\Deletions;
use App\Domain\ValueObjects\ProjectId;
use App\Infrastructure\Repositories\Eloquent\CommitEloquentModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EloquentCommitRepository implements CommitRepository
{
    /** @use ConvertsBetweenEntityAndModel<Commit, CommitEloquentModel> */
    use ConvertsBetweenEntityAndModel;

    private static function isStartDateAfterEndDate(\DateTime $startDate, \DateTime $endDate): bool
    {
        return $startDate > $endDate;
    }

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
     * 指定されたプロジェクトIDとブランチ名で最新コミット日時を取得
     */
    public function findLatestCommittedDate(ProjectId $projectId, BranchName $branchName): ?\DateTime
    {
        $result = CommitEloquentModel::where('project_id', $projectId->value)
            ->where('branch_name', $branchName->value)
            ->max('committed_date');

        return $result ? Carbon::parse($result)->toDateTime() : null;
    }

    /**
     * 指定されたプロジェクトIDとブランチ名で日付範囲内のコミットを取得
     *
     * @return Collection<int, Commit>
     *
     * @throws \InvalidArgumentException
     */
    public function findByProjectAndBranchAndDateRange(
        ProjectId $projectId,
        BranchName $branchName,
        \DateTime $startDate,
        \DateTime $endDate
    ): Collection {
        if (self::isStartDateAfterEndDate($startDate, $endDate)) {
            throw new \InvalidArgumentException('開始日は終了日より前である必要があります');
        }

        return CommitEloquentModel::where('project_id', $projectId->value)
            ->where('branch_name', $branchName->value)
            ->where('committed_date', '>=', Carbon::instance($startDate))
            ->where('committed_date', '<=', Carbon::instance($endDate))
            ->get()
            ->map($this->toEntity(...));
    }

    /**
     * エンティティに対応するEloquentモデルを検索
     */
    protected function findModel($entity)
    {
        return CommitEloquentModel::where('project_id', $entity->id->projectId->value)
            ->where('branch_name', $entity->id->branchName->value)
            ->where('sha', $entity->id->sha->value)
            ->first();
    }

    /**
     * エンティティから新しいEloquentモデルを作成
     */
    protected function createModel($entity)
    {
        $model = new CommitEloquentModel;
        $model->project_id = $entity->id->projectId->value;
        $model->branch_name = $entity->id->branchName->value;
        $model->sha = $entity->id->sha->value;

        return $model;
    }

    /**
     * EloquentモデルをCommitエンティティに変換
     */
    protected function toEntity($model)
    {
        /** @var CommitEloquentModel $model */
        return new Commit(
            id: new CommitId(
                projectId: new ProjectId($model->project_id),
                branchName: new BranchName($model->branch_name),
                sha: new CommitSha($model->sha)
            ),
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
