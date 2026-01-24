<?php

use App\Presentation\Response\Commit\UserNameBuilder;

describe('UserNameBuilder', function () {
    test('buildUserNames() が正しいユーザー名リストを構築する', function () {
        $aggregations = [
            [
                'author_name' => 'John Doe',
            ],
            [
                'author_name' => 'Jane Doe',
            ],
            [
                'author_name' => 'John Doe', // 重複
            ],
        ];

        $result = UserNameBuilder::buildUserNames($aggregations);

        expect($result)->toEqual(['Jane Doe', 'John Doe']);
    });
});
