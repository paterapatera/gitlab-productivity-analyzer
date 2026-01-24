<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

readonly class CommitSha
{
    use ComparesValue;

    private static function isValidLength(string $value): bool
    {
        return strlen($value) === 40;
    }

    private static function isHexDigit(string $value): bool
    {
        return ctype_xdigit($value);
    }

    public function __construct(
        public string $value
    ) {
        if (! self::isValidLength($this->value)) {
            throw new InvalidArgumentException('コミットSHAは40文字の16進数文字列である必要があります');
        }

        if (! self::isHexDigit($this->value)) {
            throw new InvalidArgumentException('コミットSHAは16進数文字列である必要があります');
        }
    }
}
