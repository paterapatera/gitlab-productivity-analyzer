<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

readonly class ProjectNameWithNamespace
{
    use ComparesValue;

    public function __construct(
        public string $value
    ) {
        if (trim($this->value) === '') {
            throw new InvalidArgumentException('名前空間付きプロジェクト名は空文字列にできません');
        }
    }
}
