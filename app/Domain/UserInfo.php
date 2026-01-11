<?php

namespace App\Domain;

use App\Domain\ValueObjects\AuthorEmail;
use App\Domain\ValueObjects\AuthorName;

readonly class UserInfo
{
    use ComparesProperties;

    public function __construct(
        public AuthorEmail $email,
        public AuthorName $name
    ) {}
}
