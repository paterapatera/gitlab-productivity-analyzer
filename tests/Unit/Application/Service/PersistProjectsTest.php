<?php

use App\Application\Port\ProjectRepository;
use App\Application\Service\PersistProjects;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

describe('PersistProjectsの機能', function () {
    beforeEach(function () {
        // DBファサードをモック
        DB::shouldReceive('transaction')
            ->andReturnUsing(function ($callback) {
                return $callback();
            });
    });

    test('複数のプロジェクトを一括保存する', function () {
        $projects = collect([
            createProject(1, 'group/project1'),
            createProject(2, 'group/project2'),
            createProject(3, 'group/project3'),
        ]);

        $repository = Mockery::mock(ProjectRepository::class);
        $repository->shouldReceive('saveMany')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg instanceof Collection && $arg->count() === 3;
            }));

        $useCase = new PersistProjects($repository);

        $useCase->execute($projects);
    });

    test('空のプロジェクトリストを渡してもエラーにならない', function () {
        $repository = Mockery::mock(ProjectRepository::class);
        $repository->shouldReceive('saveMany')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg instanceof Collection && $arg->count() === 0;
            }));

        $useCase = new PersistProjects($repository);

        $projects = collect([]);
        $useCase->execute($projects);
    });
});
