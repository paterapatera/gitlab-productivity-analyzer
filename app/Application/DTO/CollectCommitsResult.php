<?php

namespace App\Application\DTO;

/**
 * コミット収集操作の結果を表す DTO
 */
readonly class CollectCommitsResult
{
    public function __construct(
        public int $collectedCount,
        public int $savedCount,
        public bool $hasErrors = false,
        public ?string $errorMessage = null
    ) {}
}
