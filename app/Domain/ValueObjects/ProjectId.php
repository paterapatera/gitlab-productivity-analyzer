<?php

namespace App\Domain\ValueObjects;

readonly class ProjectId
{
    public function __construct(
        public int $value
    ) {
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
