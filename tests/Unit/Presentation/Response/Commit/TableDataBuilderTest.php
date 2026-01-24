<?php

use App\Presentation\Response\Commit\TableDataBuilder;

describe('TableDataBuilder', function () {
    test('buildTableData() が正しい表データを構築する', function () {
        $aggregations = [
            [
                'project_id' => 1,
                'branch_name' => 'main',
                'author_email' => 'john@example.com',
                'author_name' => 'John Doe',
                'year' => 2024,
                'month' => 1,
                'total_additions' => 100,
                'total_deletions' => 50,
                'commit_count' => 5,
            ],
        ];

        $result = TableDataBuilder::buildTableData($aggregations);

        expect($result)->toHaveCount(1);
        expect($result[0])->toHaveKeys(['userKey', 'userName', 'months']);
        expect($result[0]['userKey'])->toBe('1-main-john@example.com');
        expect($result[0]['userName'])->toBe('John Doe');
        expect($result[0]['months'])->toHaveKey(1);
        expect($result[0]['months'][1])->toBe(150); // 100 + 50
    });
});
