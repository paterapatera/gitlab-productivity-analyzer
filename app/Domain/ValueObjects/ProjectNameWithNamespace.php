<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

readonly class ProjectNameWithNamespace
{
    public function __construct(
        public string $value
    ) {
        if (trim($this->value) === '') {
            throw new InvalidArgumentException('名前空間付きプロジェクト名は空文字列にできません');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
