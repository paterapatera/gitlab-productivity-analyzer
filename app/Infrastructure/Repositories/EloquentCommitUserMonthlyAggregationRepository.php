<?php

namespace App\Infrastructure\Repositories;

use App\Application\Port\CommitUserMonthlyAggregationRepository;
use App\Domain\CommitUserMonthlyAggregation;
use App\Domain\UserInfo;
use App\Domain\ValueObjects\Additions;
use App\Domain\ValueObjects\AggregationMonth;
use App\Domain\ValueObjects\AggregationYear;
use App\Domain\ValueObjects\AuthorEmail;
use App\Domain\ValueObjects\AuthorName;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\CommitCount;
use App\Domain\ValueObjects\CommitUserMonthlyAggregationId;
use App\Domain\ValueObjects\Deletions;
use App\Domain\ValueObjects\ProjectId;
use App\Infrastructure\Repositories\Eloquent\CommitUserMonthlyAggregationEloquentModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EloquentCommitUserMonthlyAggregationRepository implements CommitUserMonthlyAggregationRepository
{
    /** @use ConvertsBetweenEntityAndModel<CommitUserMonthlyAggregation, CommitUserMonthlyAggregationEloquentModel> */
    use ConvertsBetweenEntityAndModel;

    /**
     * 集計データを保存または更新
     */
    public function save(CommitUserMonthlyAggregation $aggregation): CommitUserMonthlyAggregation
    {
        return $this->saveEntity($aggregation);
    }

    /**
     * 複数の集計データを一括保存または更新
     *
     * @param  Collection<int, CommitUserMonthlyAggregation>  $aggregations
     */
    public function saveMany(Collection $aggregations): void
    {
        $this->saveManyEntities($aggregations);
    }

    /**
     * 指定されたプロジェクトIDとブランチ名で最終集計月を取得
     */
    public function findLatestAggregationMonth(
        ProjectId $projectId,
        BranchName $branchName
    ): ?Carbon {
        $model = CommitUserMonthlyAggregationEloquentModel::where('project_id', $projectId->value)
            ->where('branch_name', $branchName->value)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();

        if ($model === null) {
            return null;
        }

        return Carbon::create($model->year, $model->month, 1);
    }

    /**
     * 指定されたプロジェクトIDとブランチ名で集計データを取得
     *
     * @param  array<int>|null  $months
     * @return Collection<int, CommitUserMonthlyAggregation>
     */
    public function findByProjectAndBranch(
        ProjectId $projectId,
        BranchName $branchName,
        ?int $year = null,
        ?array $months = null,
        ?string $authorEmail = null
    ): Collection {
        $query = CommitUserMonthlyAggregationEloquentModel::where('project_id', $projectId->value)
            ->where('branch_name', $branchName->value);

        if ($year !== null) {
            $query->where('year', $year);
        }

        if ($months !== null && count($months) > 0) {
            $query->whereIn('month', $months);
        }

        if ($authorEmail !== null) {
            $query->where('author_email', $authorEmail);
        }

        return $query->get()
            ->map($this->toEntity(...));
    }

    /**
     * 利用可能なユーザー一覧を取得
     *
     * @return Collection<int, UserInfo>
     */
    public function findAllUsers(): Collection
    {
        return CommitUserMonthlyAggregationEloquentModel::select('author_email', 'author_name')
            ->distinct()
            ->orderBy('author_name')
            ->get()
            ->map(function ($model) {
                return new UserInfo(
                    email: new AuthorEmail($model->author_email),
                    name: new AuthorName($model->author_name)
                );
            });
    }

    /**
     * 利用可能な年一覧を取得
     *
     * @return Collection<int, int>
     */
    public function findAvailableYears(): Collection
    {
        return CommitUserMonthlyAggregationEloquentModel::select('year')
            ->distinct()
            ->orderBy('year')
            ->pluck('year');
    }

    /**
     * ユーザー配列と年でフィルタリングして集計データを取得
     * プロジェクト・ブランチは指定しない（全リポジトリから取得）
     *
     * @param  array<string>  $authorEmails  ユーザーメールアドレスの配列。空配列`[]`の場合は全ユーザーを取得（フィルタリングなし）。nullは使用しない（常に配列として受け取る）
     * @param  int|null  $year  年。nullの場合は全年を取得（フィルタリングなし）
     * @return Collection<int, CommitUserMonthlyAggregation>
     */
    public function findByUsersAndYear(array $authorEmails, ?int $year): Collection
    {
        $query = CommitUserMonthlyAggregationEloquentModel::query();

        if (count($authorEmails) > 0) {
            $query->whereIn('author_email', $authorEmails);
        }

        if ($year !== null) {
            $query->where('year', $year);
        }

        return $query->get()
            ->map($this->toEntity(...));
    }

    /**
     * エンティティに対応するEloquentモデルを検索
     */
    protected function findModel($entity)
    {
        /** @var CommitUserMonthlyAggregation $entity */
        return CommitUserMonthlyAggregationEloquentModel::where('project_id', $entity->id->projectId->value)
            ->where('branch_name', $entity->id->branchName->value)
            ->where('author_email', $entity->id->authorEmail->value)
            ->where('year', $entity->id->year->value)
            ->where('month', $entity->id->month->value)
            ->first();
    }

    /**
     * エンティティから新しいEloquentモデルを作成
     */
    protected function createModel($entity)
    {
        /** @var CommitUserMonthlyAggregation $entity */
        $model = new CommitUserMonthlyAggregationEloquentModel;
        $model->project_id = $entity->id->projectId->value;
        $model->branch_name = $entity->id->branchName->value;
        $authorEmail = $entity->id->authorEmail->value;
        assert($authorEmail !== null, 'author_email must not be null');
        $model->author_email = $authorEmail;
        $model->year = $entity->id->year->value;
        $model->month = $entity->id->month->value;

        return $model;
    }

    /**
     * EloquentモデルをCommitUserMonthlyAggregationエンティティに変換
     */
    protected function toEntity($model)
    {
        /** @var CommitUserMonthlyAggregationEloquentModel $model */
        return new CommitUserMonthlyAggregation(
            id: new CommitUserMonthlyAggregationId(
                projectId: new ProjectId($model->project_id),
                branchName: new BranchName($model->branch_name),
                authorEmail: new AuthorEmail($model->author_email),
                year: new AggregationYear($model->year),
                month: new AggregationMonth($model->month)
            ),
            totalAdditions: new Additions($model->total_additions),
            totalDeletions: new Deletions($model->total_deletions),
            commitCount: new CommitCount($model->commit_count),
            authorName: new AuthorName($model->author_name)
        );
    }

    /**
     * CommitUserMonthlyAggregationエンティティからEloquentモデルを更新
     */
    protected function updateModelFromEntity($model, $entity): void
    {
        /** @var CommitUserMonthlyAggregationEloquentModel $model */
        /** @var CommitUserMonthlyAggregation $entity */
        $model->total_additions = $entity->totalAdditions->value;
        $model->total_deletions = $entity->totalDeletions->value;
        $model->commit_count = $entity->commitCount->value;
        $model->author_name = $entity->authorName->value;
    }
}
