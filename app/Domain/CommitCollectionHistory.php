<?php

namespace App\Domain;

use App\Domain\ValueObjects\CommitCollectionHistoryId;
use App\Domain\ValueObjects\CommittedDate;

readonly class CommitCollectionHistory
{
    use ComparesProperties;

    public function __construct(
        public CommitCollectionHistoryId $id,
        public CommittedDate $latestCommittedDate
    ) {}
}
