<?php

use App\Domain\ValueObjects\AggregationMonth;
use App\Domain\ValueObjects\AggregationYear;
use App\Domain\ValueObjects\AuthorEmail;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\CommitUserMonthlyAggregationId;
use App\Domain\ValueObjects\ProjectId;

test('すべてのフィールドでCommitUserMonthlyAggregationIdを作成できる', function () {
    $id = new CommitUserMonthlyAggregationId(
        projectId: new ProjectId(123),
        branchName: new BranchName('main'),
        authorEmail: new AuthorEmail('test@example.com'),
        year: new AggregationYear(2024),
        month: new AggregationMonth(1)
    );

    expect($id->projectId)->toBeInstanceOf(ProjectId::class);
    expect($id->branchName)->toBeInstanceOf(BranchName::class);
    expect($id->authorEmail)->toBeInstanceOf(AuthorEmail::class);
    expect($id->year)->toBeInstanceOf(AggregationYear::class);
    expect($id->month)->toBeInstanceOf(AggregationMonth::class);
});

test('同じ値のCommitUserMonthlyAggregationIdは等価である', function () {
    $id1 = new CommitUserMonthlyAggregationId(
        projectId: new ProjectId(123),
        branchName: new BranchName('main'),
        authorEmail: new AuthorEmail('test@example.com'),
        year: new AggregationYear(2024),
        month: new AggregationMonth(1)
    );

    $id2 = new CommitUserMonthlyAggregationId(
        projectId: new ProjectId(123),
        branchName: new BranchName('main'),
        authorEmail: new AuthorEmail('test@example.com'),
        year: new AggregationYear(2024),
        month: new AggregationMonth(1)
    );

    expect($id1->equals($id2))->toBeTrue();
});

test('projectIdが異なるCommitUserMonthlyAggregationIdは等価でない', function () {
    $id1 = new CommitUserMonthlyAggregationId(
        projectId: new ProjectId(123),
        branchName: new BranchName('main'),
        authorEmail: new AuthorEmail('test@example.com'),
        year: new AggregationYear(2024),
        month: new AggregationMonth(1)
    );

    $id2 = new CommitUserMonthlyAggregationId(
        projectId: new ProjectId(456),
        branchName: new BranchName('main'),
        authorEmail: new AuthorEmail('test@example.com'),
        year: new AggregationYear(2024),
        month: new AggregationMonth(1)
    );

    expect($id1->equals($id2))->toBeFalse();
});

test('branchNameが異なるCommitUserMonthlyAggregationIdは等価でない', function () {
    $id1 = new CommitUserMonthlyAggregationId(
        projectId: new ProjectId(123),
        branchName: new BranchName('main'),
        authorEmail: new AuthorEmail('test@example.com'),
        year: new AggregationYear(2024),
        month: new AggregationMonth(1)
    );

    $id2 = new CommitUserMonthlyAggregationId(
        projectId: new ProjectId(123),
        branchName: new BranchName('develop'),
        authorEmail: new AuthorEmail('test@example.com'),
        year: new AggregationYear(2024),
        month: new AggregationMonth(1)
    );

    expect($id1->equals($id2))->toBeFalse();
});

test('authorEmailが異なるCommitUserMonthlyAggregationIdは等価でない', function () {
    $id1 = new CommitUserMonthlyAggregationId(
        projectId: new ProjectId(123),
        branchName: new BranchName('main'),
        authorEmail: new AuthorEmail('test@example.com'),
        year: new AggregationYear(2024),
        month: new AggregationMonth(1)
    );

    $id2 = new CommitUserMonthlyAggregationId(
        projectId: new ProjectId(123),
        branchName: new BranchName('main'),
        authorEmail: new AuthorEmail('other@example.com'),
        year: new AggregationYear(2024),
        month: new AggregationMonth(1)
    );

    expect($id1->equals($id2))->toBeFalse();
});

test('yearが異なるCommitUserMonthlyAggregationIdは等価でない', function () {
    $id1 = new CommitUserMonthlyAggregationId(
        projectId: new ProjectId(123),
        branchName: new BranchName('main'),
        authorEmail: new AuthorEmail('test@example.com'),
        year: new AggregationYear(2024),
        month: new AggregationMonth(1)
    );

    $id2 = new CommitUserMonthlyAggregationId(
        projectId: new ProjectId(123),
        branchName: new BranchName('main'),
        authorEmail: new AuthorEmail('test@example.com'),
        year: new AggregationYear(2025),
        month: new AggregationMonth(1)
    );

    expect($id1->equals($id2))->toBeFalse();
});

test('monthが異なるCommitUserMonthlyAggregationIdは等価でない', function () {
    $id1 = new CommitUserMonthlyAggregationId(
        projectId: new ProjectId(123),
        branchName: new BranchName('main'),
        authorEmail: new AuthorEmail('test@example.com'),
        year: new AggregationYear(2024),
        month: new AggregationMonth(1)
    );

    $id2 = new CommitUserMonthlyAggregationId(
        projectId: new ProjectId(123),
        branchName: new BranchName('main'),
        authorEmail: new AuthorEmail('test@example.com'),
        year: new AggregationYear(2024),
        month: new AggregationMonth(2)
    );

    expect($id1->equals($id2))->toBeFalse();
});
