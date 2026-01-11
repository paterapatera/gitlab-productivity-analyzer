<?php

use App\Application\DTO\CollectCommitsResult;

describe('CollectCommitsResultの機能', function () {
    test('収集数と保存数を設定できる', function () {
        $result = new CollectCommitsResult(
            collectedCount: 10,
            savedCount: 10
        );

        expect($result->collectedCount)->toBe(10);
        expect($result->savedCount)->toBe(10);
        expect($result->hasErrors)->toBeFalse();
        expect($result->errorMessage)->toBeNull();
    });

    test('エラー情報を設定できる', function () {
        $result = new CollectCommitsResult(
            collectedCount: 0,
            savedCount: 0,
            hasErrors: true,
            errorMessage: 'Test error message'
        );

        expect($result->collectedCount)->toBe(0);
        expect($result->savedCount)->toBe(0);
        expect($result->hasErrors)->toBeTrue();
        expect($result->errorMessage)->toBe('Test error message');
    });

    test('デフォルト値が正しく設定される', function () {
        $result = new CollectCommitsResult(
            collectedCount: 5,
            savedCount: 5
        );

        expect($result->hasErrors)->toBeFalse();
        expect($result->errorMessage)->toBeNull();
    });
});
