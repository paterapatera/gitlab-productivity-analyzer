<?php

namespace App\Domain\ValueObjects;

readonly class DefaultBranch
{
    public function __construct(
        public ?string $value
    ) {}

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
