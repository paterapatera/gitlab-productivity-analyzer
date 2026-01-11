<?php

namespace App\Application\Port;

use App\Domain\Commit;
use Illuminate\Support\Collection;

/**
 * コミットの永続化を提供するポート
 */
interface CommitRepository
{
    /**
     * コミットを保存または更新
     *
     * @param  Commit  $commit  保存するコミット
     * @return Commit 保存されたコミット
     */
    public function save(Commit $commit): Commit;

    /**
     * 複数のコミットを一括保存または更新
     *
     * @param  Collection<int, Commit>  $commits  保存するコミットのコレクション
     */
    public function saveMany(Collection $commits): void;
}
