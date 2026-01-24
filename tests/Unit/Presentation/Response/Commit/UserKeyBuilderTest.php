<?php

use App\Presentation\Response\Commit\UserKeyBuilder;

describe('UserKeyBuilder', function () {
    test('getUserKey() が正しいキーを生成する', function () {
        $agg = ['project_id' => 1, 'branch_name' => 'main', 'author_email' => 'john@example.com'];
        expect(UserKeyBuilder::getUserKey($agg))->toBe('1-main-john@example.com');
    });
});
