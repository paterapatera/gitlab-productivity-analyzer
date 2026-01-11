<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

readonly class Deletions
{
    use ComparesValue;

    public function __construct(
        public int $value
    ) {
        if ($this->value < 0) {
            throw new InvalidArgumentException('削除行数は0以上の整数である必要があります');
        }
    }
}
