<?php

namespace App\Application\Port;

use App\Domain\CommitUserMonthlyAggregation;
use App\Domain\UserInfo;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\ProjectId;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * 集計データの永続化を提供するポート
 */
interface CommitUserMonthlyAggregationRepository
{
    /**
     * 集計データを保存または更新
     *
     * @param  CommitUserMonthlyAggregation  $aggregation  保存する集計データ
     * @return CommitUserMonthlyAggregation 保存された集計データ
     */
    public function save(CommitUserMonthlyAggregation $aggregation): CommitUserMonthlyAggregation;

    /**
     * 複数の集計データを一括保存または更新
     *
     * @param  Collection<int, CommitUserMonthlyAggregation>  $aggregations  保存する集計データのコレクション
     */
    public function saveMany(Collection $aggregations): void;

    /**
     * 指定されたプロジェクトIDとブランチ名で最終集計月を取得
     *
     * @param  ProjectId  $projectId  プロジェクトID
     * @param  BranchName  $branchName  ブランチ名
     * @return Carbon|null 最終集計月（集計データが存在しない場合は null）
     */
    public function findLatestAggregationMonth(
        ProjectId $projectId,
        BranchName $branchName
    ): ?Carbon;

    /**
     * 指定されたプロジェクトIDとブランチ名で集計データを取得
     *
     * @param  ProjectId  $projectId  プロジェクトID
     * @param  BranchName  $branchName  ブランチ名
     * @param  int|null  $year  年（オプション）
     * @param  array<int>|null  $months  月の配列（オプション）
     * @param  string|null  $authorEmail  作成者メール（オプション）
     * @return Collection<int, CommitUserMonthlyAggregation> 集計データのコレクション
     */
    public function findByProjectAndBranch(
        ProjectId $projectId,
        BranchName $branchName,
        ?int $year = null,
        ?array $months = null,
        ?string $authorEmail = null
    ): Collection;

    /**
     * 利用可能なユーザー一覧を取得
     *
     * @return Collection<int, UserInfo> ユーザー情報エンティティのコレクション
     */
    public function findAllUsers(): Collection;

    /**
     * 利用可能な年一覧を取得
     *
     * @return Collection<int, int> 年のコレクション（昇順ソート済み）
     */
    public function findAvailableYears(): Collection;

    /**
     * ユーザー配列と年でフィルタリングして集計データを取得
     * プロジェクト・ブランチは指定しない（全リポジトリから取得）
     *
     * @param  array<string>  $authorEmails  ユーザーメールアドレスの配列。空配列`[]`の場合は全ユーザーを取得（フィルタリングなし）。nullは使用しない（常に配列として受け取る）
     * @param  int|null  $year  年。nullの場合は全年を取得（フィルタリングなし）
     * @return Collection<int, CommitUserMonthlyAggregation> 集計データのコレクション
     */
    public function findByUsersAndYear(array $authorEmails, ?int $year): Collection;
}
