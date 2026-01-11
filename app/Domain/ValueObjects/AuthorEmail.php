<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

readonly class AuthorEmail
{
    use ComparesValue;

    public function __construct(
        public ?string $value
    ) {
        if ($this->value !== null && strlen($this->value) > 255) {
            throw new InvalidArgumentException('作成者メールは255文字以下である必要があります');
        }
    }
}
