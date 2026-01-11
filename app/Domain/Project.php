<?php

namespace App\Domain;

use App\Domain\ValueObjects\DefaultBranch;
use App\Domain\ValueObjects\ProjectDescription;
use App\Domain\ValueObjects\ProjectId;
use App\Domain\ValueObjects\ProjectNameWithNamespace;

readonly class Project
{
    use ComparesProperties;

    public function __construct(
        public ProjectId $id,
        public ProjectNameWithNamespace $nameWithNamespace,
        public ProjectDescription $description = new ProjectDescription(null),
        public DefaultBranch $defaultBranch = new DefaultBranch(null)
    ) {}
}
