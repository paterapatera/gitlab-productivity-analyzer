<?php

use App\Presentation\Response\Commit\ChartDataBuilder;

describe('ChartDataBuilder', function () {
    test('buildChartData() が正しいグラフデータを構築する', function () {
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
            [
                'project_id' => 1,
                'branch_name' => 'main',
                'author_email' => 'jane@example.com',
                'author_name' => 'Jane Doe',
                'year' => 2024,
                'month' => 2,
                'total_additions' => 200,
                'total_deletions' => 100,
                'commit_count' => 10,
            ],
        ];

        $result = ChartDataBuilder::buildChartData($aggregations);

        expect($result)->toHaveCount(12);
        expect($result[0])->toHaveKey('month');
        expect($result[0]['month'])->toBe('1月');
        expect($result[0])->toHaveKey('John Doe_additions');
        expect($result[0]['John Doe_additions'])->toBe(100);
        expect($result[0]['John Doe_deletions'])->toBe(50);
        expect($result[1]['Jane Doe_additions'])->toBe(200);
    });
});
