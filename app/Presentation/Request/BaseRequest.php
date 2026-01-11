<?php

namespace App\Presentation\Request;

use Illuminate\Http\Request;

/**
 * プレゼンテーション層リクエストの基底クラス
 *
 * このクラスは、すべてのプレゼンテーション層リクエストで共通の機能を提供します。
 */
abstract class BaseRequest
{
    public function __construct(
        protected readonly Request $request
    ) {}

    /**
     * HTTPリクエストを取得
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * バリデーションルールを取得
     *
     * @return array<string, mixed>
     */
    abstract public function rules(): array;
}
