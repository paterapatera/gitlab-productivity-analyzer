<?php

namespace App\Presentation\Controller;

use Illuminate\Http\Request;
use Inertia\Response;

/**
 * プレゼンテーション層コントローラーの基底クラス
 *
 * このクラスは、すべてのプレゼンテーション層コントローラーで共通の機能を提供します。
 * - フラッシュメッセージの処理
 * - エラーハンドリングの統一パターン
 */
abstract class BaseController
{
    /**
     * Inertiaレスポンスにフラッシュメッセージを追加
     *
     * @param  array<string, mixed>  $props  既存のprops
     * @param  Request  $request  HTTPリクエスト
     * @return array<string, mixed> フラッシュメッセージが追加されたprops
     */
    protected function addFlashMessages(array $props, Request $request): array
    {
        if ($request->session()->has('error')) {
            $props['error'] = $request->session()->get('error');
        }
        if ($request->session()->has('success')) {
            $props['success'] = $request->session()->get('success');
        }

        return $props;
    }

    /**
     * エラーハンドリング付きでInertiaレスポンスを返す
     *
     * @param  callable  $callback  レスポンスを生成するコールバック
     * @param  string  $errorMessage  エラーメッセージ
     * @return Response Inertiaレスポンス
     */
    protected function renderWithErrorHandling(callable $callback, string $errorMessage): Response
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            abort(500, $errorMessage);
        }
    }
}
