<?php

namespace App\Domain\ValueObjects;

readonly class ProjectDescription
{
    use ComparesValue;

    public function __construct(
        public ?string $value
    ) {}
}
