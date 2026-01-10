<?php

use App\Application\Port\GitApi;
use App\Application\Service\GetProjects;
use App\Domain\Project;
use Illuminate\Support\Collection;

describe('GetProjectsの機能', function () {
    test('GitApiから取得したプロジェクトを返す', function () {
        $projects = collect([
            createProject(1, 'group/project', 'Test project', 'main'),
        ]);

        $mockClient = Mockery::mock(GitApi::class);
        $mockClient->shouldReceive('getProjects')
            ->once()
            ->andReturn($projects);

        $useCase = new GetProjects($mockClient);

        $result = $useCase->execute();

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result)->toHaveCount(1);
        expect($result[0])->toBeInstanceOf(Project::class);
        expect($result[0]->id->value)->toBe(1);
        expect($result[0]->nameWithNamespace->value)->toBe('group/project');
    });

    test('空のコレクションを返すことができる', function () {
        $mockClient = Mockery::mock(GitApi::class);
        $mockClient->shouldReceive('getProjects')
            ->once()
            ->andReturn(collect([]));

        $useCase = new GetProjects($mockClient);

        $result = $useCase->execute();

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result)->toHaveCount(0);
    });

    test('複数のプロジェクトを返すことができる', function () {
        $projects = collect([
            createProject(1, 'group/project1'),
            createProject(2, 'group/project2'),
            createProject(3, 'group/project3'),
        ]);

        $mockClient = Mockery::mock(GitApi::class);
        $mockClient->shouldReceive('getProjects')
            ->once()
            ->andReturn($projects);

        $useCase = new GetProjects($mockClient);

        $result = $useCase->execute();

        expect($result)->toHaveCount(3);
    });
});
