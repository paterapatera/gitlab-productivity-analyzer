<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

readonly class BranchName
{
    use ComparesValue;

    private static function isEmpty(string $value): bool
    {
        return trim($value) === '';
    }

    private static function isTooLong(string $value): bool
    {
        return strlen($value) > 255;
    }

    public function __construct(
        public string $value
    ) {
        if (self::isEmpty($this->value)) {
            throw new InvalidArgumentException('ブランチ名は空文字列にできません');
        }

        if (self::isTooLong($this->value)) {
            throw new InvalidArgumentException('ブランチ名は255文字以下である必要があります');
        }
    }
}
