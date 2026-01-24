<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

readonly class Additions
{
    use ComparesValue;

    private static function isNegative(int $value): bool
    {
        return $value < 0;
    }

    public function __construct(
        public int $value
    ) {
        if (self::isNegative($this->value)) {
            throw new InvalidArgumentException('追加行数は0以上の整数である必要があります');
        }
    }
}
