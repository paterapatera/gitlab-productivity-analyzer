<?php

namespace App\Application\DTO;

/**
 * プロジェクト同期操作の結果を表す DTO
 */
readonly class SyncResult
{
    public function __construct(
        public int $syncedCount,
        public int $deletedCount,
        public bool $hasErrors = false,
        public ?string $errorMessage = null
    ) {}
}
