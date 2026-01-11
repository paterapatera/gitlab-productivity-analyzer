<?php

namespace App\Application\Contract;

use App\Application\DTO\SyncResult;

/**
 * プロジェクト情報を同期する契約
 */
interface SyncProjects
{
    /**
     * プロジェクト情報を同期
     *
     * @return SyncResult 同期結果
     */
    public function execute(): SyncResult;
}
