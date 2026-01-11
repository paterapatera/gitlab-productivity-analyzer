<?php

namespace App\Application\Contract;

use App\Application\DTO\CollectCommitsResult;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\ProjectId;

/**
 * コミットを収集・永続化する契約
 */
interface CollectCommits
{
    /**
     * プロジェクトとブランチを指定してコミットを収集・永続化
     *
     * @param  ProjectId  $projectId  プロジェクトID
     * @param  BranchName  $branchName  ブランチ名
     * @param  \DateTime|null  $sinceDate  開始日（オプショナル、指定された場合はこの日以降のコミットのみを収集）
     * @return CollectCommitsResult 収集結果
     */
    public function execute(
        ProjectId $projectId,
        BranchName $branchName,
        ?\DateTime $sinceDate = null
    ): CollectCommitsResult;
}
