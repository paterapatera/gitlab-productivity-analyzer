<?php

namespace App\Application\Contract;

use App\Application\DTO\SyncResult;

interface SyncProjects
{
    /**
     * プロジェクト情報を同期
     */
    public function execute(): SyncResult;
}
