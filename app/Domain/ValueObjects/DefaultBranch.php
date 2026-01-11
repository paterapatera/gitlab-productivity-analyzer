<?php

namespace App\Domain\ValueObjects;

readonly class DefaultBranch
{
    use ComparesValue;

    public function __construct(
        public ?string $value
    ) {}
}
