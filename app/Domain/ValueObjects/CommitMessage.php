<?php

namespace App\Domain\ValueObjects;

readonly class CommitMessage
{
    use ComparesValue;

    public function __construct(
        public string $value
    ) {}
}
