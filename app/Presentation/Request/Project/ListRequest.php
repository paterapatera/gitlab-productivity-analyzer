<?php

namespace App\Presentation\Request\Project;

use Illuminate\Http\Request;

class ListRequest
{
    public function __construct(
        private readonly Request $request
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
    public function rules(): array
    {
        // GETリクエストなので、現時点ではバリデーションルールは不要
        return [];
    }
}
