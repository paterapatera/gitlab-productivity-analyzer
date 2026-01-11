<?php

namespace App\Application\Service;

use Illuminate\Support\Facades\DB;

/**
 * アプリケーションサービスの基底クラス
 *
 * このクラスは、すべてのアプリケーションサービスで共通の機能を提供します。
 * - トランザクション管理
 * - エラーハンドリングの統一パターン
 */
abstract class BaseService
{
    /**
     * トランザクション内で処理を実行
     *
     * @param  \Closure(\Illuminate\Database\Connection): mixed  $callback  実行する処理
     * @return mixed コールバックの戻り値
     */
    protected function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    /**
     * 例外をキャッチしてエラーメッセージを返す
     *
     * @param  callable  $callback  実行する処理
     * @param  callable  $onError  エラー時の処理（エラーメッセージを受け取る）
     * @return mixed コールバックの戻り値、またはエラー時の戻り値
     */
    protected function handleErrors(callable $callback, callable $onError): mixed
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            return $onError($e->getMessage());
        }
    }
}
