<?php

namespace App\Domain;

use App\Domain\ValueObjects\Additions;
use App\Domain\ValueObjects\AuthorEmail;
use App\Domain\ValueObjects\AuthorName;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\CommitMessage;
use App\Domain\ValueObjects\CommitSha;
use App\Domain\ValueObjects\CommittedDate;
use App\Domain\ValueObjects\Deletions;
use App\Domain\ValueObjects\ProjectId;

readonly class Commit
{
    use ComparesProperties;

    public function __construct(
        public ProjectId $projectId,
        public BranchName $branchName,
        public CommitSha $sha,
        public CommitMessage $message,
        public CommittedDate $committedDate,
        public AuthorName $authorName,
        public AuthorEmail $authorEmail,
        public Additions $additions,
        public Deletions $deletions
    ) {}
}
