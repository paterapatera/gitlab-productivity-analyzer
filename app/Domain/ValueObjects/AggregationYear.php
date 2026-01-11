<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

readonly class AggregationYear
{
    use ComparesValue;

    public function __construct(
        public int $value
    ) {
        if ($this->value < 1 || $this->value > 9999) {
            throw new InvalidArgumentException('年は1以上9999以下である必要があります');
        }
    }
}
