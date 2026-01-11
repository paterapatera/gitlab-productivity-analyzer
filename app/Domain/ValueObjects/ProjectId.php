<?php

namespace App\Domain\ValueObjects;

readonly class ProjectId
{
    use ComparesValue;

    public function __construct(
        public int $value
    ) {}
}
