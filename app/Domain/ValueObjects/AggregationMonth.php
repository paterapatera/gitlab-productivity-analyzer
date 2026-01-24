<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

readonly class AggregationMonth
{
    use ComparesValue;

    private static function isValidMonth(int $value): bool
    {
        return $value >= 1 && $value <= 12;
    }

    public function __construct(
        public int $value
    ) {
        if (! self::isValidMonth($this->value)) {
            throw new InvalidArgumentException('月は1以上12以下である必要があります');
        }
    }
}
