<?php

namespace App\Application\Port;

use App\Domain\CommitCollectionHistory;
use App\Domain\ValueObjects\CommitCollectionHistoryId;
use Illuminate\Support\Collection;

/**
 * 収集履歴の永続化を提供するポート
 */
interface CommitCollectionHistoryRepository
{
    /**
     * 収集履歴を保存または更新
     *
     * @param  CommitCollectionHistory  $history  保存する収集履歴
     * @return CommitCollectionHistory 保存された収集履歴
     */
    public function save(CommitCollectionHistory $history): CommitCollectionHistory;

    /**
     * プロジェクトIDとブランチ名で収集履歴を取得
     *
     * @param  CommitCollectionHistoryId  $id  収集履歴ID
     * @return CommitCollectionHistory|null 収集履歴（存在しない場合は null）
     */
    public function findById(CommitCollectionHistoryId $id): ?CommitCollectionHistory;

    /**
     * すべての収集履歴を取得
     *
     * @return Collection<int, CommitCollectionHistory>
     */
    public function findAll(): Collection;
}
