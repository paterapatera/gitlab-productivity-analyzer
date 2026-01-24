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
use App\Presentation\Response\Commit\AggregationUtils;

describe('AggregationUtils', function () {
    test('isSelectionValid() が正しく判定する', function () {
        expect(AggregationUtils::isSelectionValid(1, 'main'))->toBeTrue();
        expect(AggregationUtils::isSelectionValid(null, 'main'))->toBeFalse();
        expect(AggregationUtils::isSelectionValid(1, null))->toBeFalse();
        expect(AggregationUtils::isSelectionValid(null, null))->toBeFalse();
    });

    test('isBranchMatching() が正しく判定する', function () {
        $branch = ['project_id' => 1, 'branch_name' => 'main'];
        expect(AggregationUtils::isBranchMatching($branch, 1, 'main'))->toBeTrue();
        expect(AggregationUtils::isBranchMatching($branch, 2, 'main'))->toBeFalse();
        expect(AggregationUtils::isBranchMatching($branch, 1, 'develop'))->toBeFalse();
    });

    test('mapAggregationToArray() が正しくマッピングする', function () {
        $aggregation = new CommitUserMonthlyAggregation(
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
        );

        $result = AggregationUtils::mapAggregationToArray($aggregation);

        expect($result)->toEqual([
            'project_id' => 1,
            'branch_name' => 'main',
            'author_email' => 'john@example.com',
            'author_name' => 'John Doe',
            'year' => 2024,
            'month' => 1,
            'total_additions' => 100,
            'total_deletions' => 50,
            'commit_count' => 5,
        ]);
    });

    test('mapProjectToArray() が正しくマッピングする', function () {
        $project = new Project(
            id: new ProjectId(1),
            nameWithNamespace: new ProjectNameWithNamespace('group/project1'),
            description: new ProjectDescription('Test project'),
            defaultBranch: new DefaultBranch('main')
        );

        $result = AggregationUtils::mapProjectToArray($project);

        expect($result)->toEqual([
            'id' => 1,
            'name_with_namespace' => 'group/project1',
        ]);
    });

    test('compareAggregations() が正しく比較する', function () {
        $a = ['project_id' => 1, 'branch_name' => 'main', 'author_name' => 'John'];
        $b = ['project_id' => 1, 'branch_name' => 'main', 'author_name' => 'Jane'];

        expect(AggregationUtils::compareAggregations($a, $b))->toBeGreaterThan(0); // John > Jane
        expect(AggregationUtils::compareAggregations($b, $a))->toBeLessThan(0); // Jane < John

        $c = ['project_id' => 2, 'branch_name' => 'main', 'author_name' => 'John'];
        expect(AggregationUtils::compareAggregations($a, $c))->toBe(-1); // 1 < 2
    });
});
