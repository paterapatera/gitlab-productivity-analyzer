<?php

namespace App\Application\Port;

use App\Domain\Commit;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\ProjectId;
use Illuminate\Support\Collection;

/**
 * コミットの永続化を提供するポート
 */
interface CommitRepository
{
    /**
     * コミットを保存または更新
     *
     * @param  Commit  $commit  保存するコミット
     * @return Commit 保存されたコミット
     */
    public function save(Commit $commit): Commit;

    /**
     * 複数のコミットを一括保存または更新
     *
     * @param  Collection<int, Commit>  $commits  保存するコミットのコレクション
     */
    public function saveMany(Collection $commits): void;

    /**
     * 指定されたプロジェクトIDとブランチ名で最新コミット日時を取得
     *
     * @param  ProjectId  $projectId  プロジェクトID
     * @param  BranchName  $branchName  ブランチ名
     * @return \DateTime|null 最新コミット日時（コミットが存在しない場合は null）
     */
    public function findLatestCommittedDate(ProjectId $projectId, BranchName $branchName): ?\DateTime;

    /**
     * 指定されたプロジェクトIDとブランチ名で日付範囲内のコミットを取得
     *
     * @param  ProjectId  $projectId  プロジェクトID
     * @param  BranchName  $branchName  ブランチ名
     * @param  \DateTime  $startDate  開始日時
     * @param  \DateTime  $endDate  終了日時
     * @return Collection<int, Commit> コミットのコレクション
     *
     * @throws \InvalidArgumentException 開始日が終了日より後の場合
     */
    public function findByProjectAndBranchAndDateRange(
        ProjectId $projectId,
        BranchName $branchName,
        \DateTime $startDate,
        \DateTime $endDate
    ): Collection;
}
