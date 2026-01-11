<?php

namespace App\Domain;

use App\Domain\ValueObjects\Additions;
use App\Domain\ValueObjects\AuthorName;
use App\Domain\ValueObjects\CommitCount;
use App\Domain\ValueObjects\CommitUserMonthlyAggregationId;
use App\Domain\ValueObjects\Deletions;

readonly class CommitUserMonthlyAggregation
{
    use ComparesProperties;

    public function __construct(
        public CommitUserMonthlyAggregationId $id,
        public Additions $totalAdditions,
        public Deletions $totalDeletions,
        public CommitCount $commitCount,
        public AuthorName $authorName
    ) {}
}
