<?php

namespace App\Presentation\Request\Project;

use App\Presentation\Request\BaseRequest;

class ListRequest extends BaseRequest
{
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
