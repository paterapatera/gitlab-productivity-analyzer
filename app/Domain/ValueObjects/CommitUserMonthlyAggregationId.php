<?php

namespace App\Domain\ValueObjects;

readonly class CommitUserMonthlyAggregationId
{
    public function __construct(
        public ProjectId $projectId,
        public BranchName $branchName,
        public AuthorEmail $authorEmail,
        public AggregationYear $year,
        public AggregationMonth $month
    ) {}

    /**
     * 他のCommitUserMonthlyAggregationIdと等価かどうかを判定する
     *
     * @param  self  $other  比較対象のCommitUserMonthlyAggregationId
     * @return bool 等価な場合 true、そうでない場合 false
     */
    public function equals(self $other): bool
    {
        return $this->projectId->equals($other->projectId)
            && $this->branchName->equals($other->branchName)
            && $this->authorEmail->equals($other->authorEmail)
            && $this->year->equals($other->year)
            && $this->month->equals($other->month);
    }
}
