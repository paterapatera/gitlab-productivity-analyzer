<?php

namespace App\Application\Contract;

use App\Application\DTO\AggregateCommitsResult;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\ProjectId;

/**
 * コミットデータから月次集計を生成する契約
 */
interface AggregateCommits
{
    /**
     * プロジェクトとブランチを指定してコミットデータから月次集計を生成
     *
     * @param  ProjectId  $projectId  プロジェクトID
     * @param  BranchName  $branchName  ブランチ名
     * @return AggregateCommitsResult 集計結果
     */
    public function execute(
        ProjectId $projectId,
        BranchName $branchName
    ): AggregateCommitsResult;
}
