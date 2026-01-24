<?php

use App\Domain\CommitUserMonthlyAggregation;
use App\Domain\Project;
use App\Domain\ValueObjects\Additions;
use App\Domain\ValueObjects\AggregationMonth;
use App\Domain\ValueObjects\AggregationYear;
use App\Domain\ValueObjects\AuthorEmail;
use App\Domain\ValueObjects\AuthorName;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\CommitCount;
use App\Domain\ValueObjects\CommitUserMonthlyAggregationId;
use App\Domain\ValueObjects\DefaultBranch;
use App\Domain\ValueObjects\Deletions;
use App\Domain\ValueObjects\ProjectDescription;
use App\Domain\ValueObjects\ProjectId;
use App\Domain\ValueObjects\ProjectNameWithNamespace;
use App\Presentation\Response\Commit\AggregationShowResponse;

describe('AggregationShowResponse', function () {
    test('toArray() が正しい構造を返す', function () {
        // プロジェクトの作成
        $projects = collect([
            new Project(
                id: new ProjectId(1),
                nameWithNamespace: new ProjectNameWithNamespace('group/project1'),
                description: new ProjectDescription('Test project'),
                defaultBranch: new DefaultBranch('main')
            ),
        ]);

        // ブランチの作成
        $branches = collect([
            ['project_id' => 1, 'branch_name' => 'main'],
            ['project_id' => 1, 'branch_name' => 'develop'],
        ]);

        // 年の作成
        $years = collect([2024, 2025]);

        // 集計データの作成
        $aggregations = collect([
            new CommitUserMonthlyAggregation(
                id: new CommitUserMonthlyAggregationId(
                    projectId: new ProjectId(1),
                    branchName: new BranchName('main'),
                    authorEmail: new AuthorEmail('john@example.com'),
                    year: new AggregationYear(2024),
                    month: new AggregationMonth(1)
                ),
                totalAdditions: new Additions(100),
                totalDeletions: new Deletions(50),
                commitCount: new CommitCount(5),
                authorName: new AuthorName('John Doe')
            ),
            new CommitUserMonthlyAggregation(
                id: new CommitUserMonthlyAggregationId(
                    projectId: new ProjectId(1),
                    branchName: new BranchName('main'),
                    authorEmail: new AuthorEmail('jane@example.com'),
                    year: new AggregationYear(2024),
                    month: new AggregationMonth(2)
                ),
                totalAdditions: new Additions(200),
                totalDeletions: new Deletions(100),
                commitCount: new CommitCount(10),
                authorName: new AuthorName('Jane Doe')
            ),
        ]);

        $response = new AggregationShowResponse(
            projects: $projects,
            branches: $branches,
            years: $years,
            aggregations: $aggregations,
            selectedProjectId: 1,
            selectedBranchName: 'main',
            selectedYear: 2024
        );

        $result = $response->toArray();

        expect($result)->toHaveKeys(['projects', 'branches', 'years', 'aggregations', 'chartData', 'tableData', 'userNames', 'selectedProjectId', 'selectedBranchName', 'selectedYear', 'selectedBranch']);

        // プロジェクトのチェック
        expect($result['projects'])->toHaveCount(1);
        expect($result['projects'][0])->toHaveKeys(['id', 'name_with_namespace']);

        // ブランチのチェック
        expect($result['branches'])->toHaveCount(2);

        // 年のチェック
        expect($result['years'])->toEqual([2024, 2025]);

        // 集計データのチェック（ソートされている）
        expect($result['aggregations'])->toHaveCount(2);
        expect($result['aggregations'][0]['author_email'])->toBe('jane@example.com'); // Jane が先
        expect($result['aggregations'][1]['author_email'])->toBe('john@example.com');

        // グラフデータのチェック
        expect($result['chartData'])->toHaveCount(12); // 12ヶ月
        expect($result['chartData'][0])->toHaveKey('month');
        expect($result['chartData'][0]['month'])->toBe('1月');

        // 表データのチェック
        expect($result['tableData'])->toHaveCount(2); // 2ユーザー
        expect($result['tableData'][0])->toHaveKeys(['userKey', 'userName', 'months']);

        // ユーザー名のチェック
        expect($result['userNames'])->toEqual(['Jane Doe', 'John Doe']);

        // 選択されたブランチのチェック
        expect($result['selectedBranch'])->toEqual(['project_id' => 1, 'branch_name' => 'main']);
    });

    test('選択が有効でない場合、selectedBranch は null を返す', function () {
        $response = new AggregationShowResponse(
            projects: collect(),
            branches: collect(),
            years: collect(),
            aggregations: collect(),
            selectedProjectId: null,
            selectedBranchName: null,
            selectedYear: null
        );

        $result = $response->toArray();

        expect($result['selectedBranch'])->toBeNull();
    });

    test('集計データが空の場合、適切なデフォルト値を返す', function () {
        $response = new AggregationShowResponse(
            projects: collect(),
            branches: collect(),
            years: collect(),
            aggregations: collect(),
            selectedProjectId: null,
            selectedBranchName: null,
            selectedYear: null
        );

        $result = $response->toArray();

        expect($result['aggregations'])->toBeEmpty();
        expect($result['chartData'])->toHaveCount(12); // 空の月データ
        expect($result['tableData'])->toBeEmpty();
        expect($result['userNames'])->toBeEmpty();
    });

    test('集計データがソートされている', function () {
        $aggregations = collect([
            new CommitUserMonthlyAggregation(
                id: new CommitUserMonthlyAggregationId(
                    projectId: new ProjectId(2),
                    branchName: new BranchName('main'),
                    authorEmail: new AuthorEmail('a@example.com'),
                    year: new AggregationYear(2024),
                    month: new AggregationMonth(1)
                ),
                totalAdditions: new Additions(10),
                totalDeletions: new Deletions(5),
                commitCount: new CommitCount(1),
                authorName: new AuthorName('Author A')
            ),
            new CommitUserMonthlyAggregation(
                id: new CommitUserMonthlyAggregationId(
                    projectId: new ProjectId(1),
                    branchName: new BranchName('main'),
                    authorEmail: new AuthorEmail('b@example.com'),
                    year: new AggregationYear(2024),
                    month: new AggregationMonth(1)
                ),
                totalAdditions: new Additions(20),
                totalDeletions: new Deletions(10),
                commitCount: new CommitCount(2),
                authorName: new AuthorName('Author B')
            ),
        ]);

        $response = new AggregationShowResponse(
            projects: collect(),
            branches: collect(),
            years: collect(),
            aggregations: $aggregations,
            selectedProjectId: null,
            selectedBranchName: null,
            selectedYear: null
        );

        $result = $response->toArray();

        // project_id でソートされている（1, 2）
        expect($result['aggregations'][0]['project_id'])->toBe(1);
        expect($result['aggregations'][1]['project_id'])->toBe(2);
    });
});
