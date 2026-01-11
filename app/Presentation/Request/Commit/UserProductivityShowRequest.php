<?php

namespace App\Presentation\Request\Commit;

use App\Presentation\Request\BaseRequest;

class UserProductivityShowRequest extends BaseRequest
{
    /**
     * バリデーションルールを取得
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'year' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'users' => ['nullable', 'array'],
            'users.*' => ['string', 'email'],
        ];
    }

    /**
     * 年を取得
     */
    public function getYear(): ?int
    {
        $year = $this->request->input('year');
        if ($year === null || $year === '') {
            return null;
        }

        return (int) $year;
    }

    /**
     * ユーザー配列を取得
     *
     * @return array<string>
     */
    public function getUsers(): array
    {
        $users = $this->request->input('users');
        if ($users === null || ! is_array($users)) {
            return [];
        }

        return array_filter(
            array_map('strval', $users),
            fn ($user) => $user !== ''
        );
    }
}
