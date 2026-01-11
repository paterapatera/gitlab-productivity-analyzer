<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

readonly class BranchName
{
    use ComparesValue;

    public function __construct(
        public string $value
    ) {
        if (trim($this->value) === '') {
            throw new InvalidArgumentException('ブランチ名は空文字列にできません');
        }

        if (strlen($this->value) > 255) {
            throw new InvalidArgumentException('ブランチ名は255文字以下である必要があります');
        }
    }
}
