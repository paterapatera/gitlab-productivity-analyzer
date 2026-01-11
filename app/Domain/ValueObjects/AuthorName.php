<?php

namespace App\Domain\ValueObjects;

readonly class AuthorName
{
    use ComparesValue;

    public function __construct(
        public ?string $value
    ) {}
}
