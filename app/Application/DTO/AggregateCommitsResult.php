<?php

namespace App\Application\DTO;

/**
 * コミット集計操作の結果を表す DTO
 */
readonly class AggregateCommitsResult
{
    public function __construct(
        public int $aggregatedCount,
        public bool $hasErrors = false,
        public ?string $errorMessage = null
    ) {}
}
