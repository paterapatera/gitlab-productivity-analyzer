<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

readonly class CommitSha
{
    use ComparesValue;

    public function __construct(
        public string $value
    ) {
        if (strlen($this->value) !== 40) {
            throw new InvalidArgumentException('コミットSHAは40文字の16進数文字列である必要があります');
        }

        if (! ctype_xdigit($this->value)) {
            throw new InvalidArgumentException('コミットSHAは16進数文字列である必要があります');
        }
    }
}
