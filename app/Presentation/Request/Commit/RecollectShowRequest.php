<?php

namespace App\Presentation\Request\Commit;

use App\Presentation\Request\BaseRequest;

class RecollectShowRequest extends BaseRequest
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
