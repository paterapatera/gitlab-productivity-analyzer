<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

readonly class AggregationMonth
{
    use ComparesValue;

    public function __construct(
        public int $value
    ) {
        if ($this->value < 1 || $this->value > 12) {
            throw new InvalidArgumentException('月は1以上12以下である必要があります');
        }
    }
}
