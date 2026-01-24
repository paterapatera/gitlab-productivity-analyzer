<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

readonly class AuthorEmail
{
    use ComparesValue;

    private static function isTooLong(?string $value): bool
    {
        return $value !== null && strlen($value) > 255;
    }

    public function __construct(
        public ?string $value
    ) {
        if (self::isTooLong($this->value)) {
            throw new InvalidArgumentException('作成者メールは255文字以下である必要があります');
        }
    }
}
