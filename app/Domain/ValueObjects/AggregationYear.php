<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

readonly class AggregationYear
{
    use ComparesValue;

    private static function isValidYear(int $value): bool
    {
        return $value >= 1 && $value <= 9999;
    }

    public function __construct(
        public int $value
    ) {
        if (! self::isValidYear($this->value)) {
            throw new InvalidArgumentException('年は1以上9999以下である必要があります');
        }
    }
}
