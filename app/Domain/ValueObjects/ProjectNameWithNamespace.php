<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

readonly class ProjectNameWithNamespace
{
    use ComparesValue;

    private static function isEmpty(string $value): bool
    {
        return trim($value) === '';
    }

    public function __construct(
        public string $value
    ) {
        if (self::isEmpty($this->value)) {
            throw new InvalidArgumentException('名前空間付きプロジェクト名は空文字列にできません');
        }
    }
}
