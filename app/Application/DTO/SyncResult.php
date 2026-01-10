<?php

namespace App\Application\DTO;

readonly class SyncResult
{
    public function __construct(
        public int $syncedCount,
        public int $deletedCount,
        public bool $hasErrors = false,
        public ?string $errorMessage = null
    ) {}
}
