<?php

namespace App\Domain\ValueObjects;

readonly class CommitCollectionHistoryId
{
    public function __construct(
        public ProjectId $projectId,
        public BranchName $branchName
    ) {}

    /**
     * 他のCommitCollectionHistoryIdと等価かどうかを判定する
     *
     * @param  self  $other  比較対象のCommitCollectionHistoryId
     * @return bool 等価な場合 true、そうでない場合 false
     */
    public function equals(self $other): bool
    {
        return $this->projectId->equals($other->projectId)
            && $this->branchName->equals($other->branchName);
    }
}
