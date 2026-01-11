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
}
