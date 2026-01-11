<?php

namespace App\Application\Port;

use App\Domain\Commit;
use App\Domain\Project;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\ProjectId;
use App\Infrastructure\GitLab\Exceptions\GitLabApiException;
use Illuminate\Support\Collection;

/**
 * 外部 Git API へのアクセスを提供するポート
 */
interface GitApi
{
    /**
     * 外部APIから全プロジェクトを取得
     *
     * @return Collection<int, Project>
     *
     * @throws GitLabApiException
     */
    public function getProjects(): Collection;

    /**
     * ブランチの存在を検証
     *
     * @param  ProjectId  $projectId  プロジェクトID
     * @param  BranchName  $branchName  ブランチ名
     *
     * @throws GitLabApiException ブランチが存在しない場合、またはAPIエラー
     */
    public function validateBranch(ProjectId $projectId, BranchName $branchName): void;

    /**
     * プロジェクトとブランチを指定してコミットを取得
     *
     * @param  ProjectId  $projectId  プロジェクトID
     * @param  BranchName  $branchName  ブランチ名
     * @param  \DateTime|null  $sinceDate  開始日（オプショナル、指定された場合はこの日以降のコミットのみを取得）
     * @return Collection<int, Commit> コミットのコレクション
     *
     * @throws GitLabApiException APIエラー
     */
    public function getCommits(
        ProjectId $projectId,
        BranchName $branchName,
        ?\DateTime $sinceDate = null
    ): Collection;
}
