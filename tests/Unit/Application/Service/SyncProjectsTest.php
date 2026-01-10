<?php

use App\Application\Contract\GetProjects;
use App\Application\Contract\PersistProjects;
use App\Application\DTO\SyncResult;
use App\Application\Port\ProjectRepository;
use App\Application\Service\SyncProjects as SyncProjectsService;
use App\Domain\Project;
use Illuminate\Support\Collection;

describe('SyncProjectsの機能', function () {
    test('外部APIから取得したプロジェクトを永続化し、削除されたプロジェクトを検出する', function () {
        $projects = collect([
            createProject(1, 'group/project1'),
            createProject(2, 'group/project2'),
        ]);

        $mockGetProjects = Mockery::mock(GetProjects::class);
        $mockGetProjects->shouldReceive('execute')
            ->once()
            ->andReturn($projects);

        $mockPersistProjects = Mockery::mock(PersistProjects::class);
        $mockPersistProjects->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg instanceof Collection && $arg->count() === 2;
            }));

        $mockRepository = Mockery::mock(ProjectRepository::class);
        $mockRepository->shouldReceive('findNotInProjectIds')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg instanceof Collection && $arg->count() === 2;
            }))
            ->andReturn(collect([
                createProject(3, 'group/project3'),
            ]));

        $mockRepository->shouldReceive('delete')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg instanceof Project && $arg->id->value === 3;
            }));

        $useCase = new SyncProjectsService(
            $mockGetProjects,
            $mockPersistProjects,
            $mockRepository
        );

        $result = $useCase->execute();

        expect($result)->toBeInstanceOf(SyncResult::class);
        expect($result->syncedCount)->toBe(2);
        expect($result->deletedCount)->toBe(1);
        expect($result->hasErrors)->toBeFalse();
    });

    test('削除されたプロジェクトがない場合、deletedCountは0になる', function () {
        $projects = collect([
            createProject(1, 'group/project1'),
        ]);

        $mockGetProjects = Mockery::mock(GetProjects::class);
        $mockGetProjects->shouldReceive('execute')
            ->once()
            ->andReturn($projects);

        $mockPersistProjects = Mockery::mock(PersistProjects::class);
        $mockPersistProjects->shouldReceive('execute')
            ->once();

        $mockRepository = Mockery::mock(ProjectRepository::class);
        $mockRepository->shouldReceive('findNotInProjectIds')
            ->once()
            ->andReturn(collect([]));

        $useCase = new SyncProjectsService(
            $mockGetProjects,
            $mockPersistProjects,
            $mockRepository
        );

        $result = $useCase->execute();

        expect($result)->toBeInstanceOf(SyncResult::class);
        expect($result->syncedCount)->toBe(1);
        expect($result->deletedCount)->toBe(0);
        expect($result->hasErrors)->toBeFalse();
    });

    test('外部APIから空のプロジェクトリストが返された場合、既存の全プロジェクトが削除される', function () {
        $projects = collect([]);

        $existingProjects = collect([
            createProject(1, 'group/project1'),
            createProject(2, 'group/project2'),
        ]);

        $mockGetProjects = Mockery::mock(GetProjects::class);
        $mockGetProjects->shouldReceive('execute')
            ->once()
            ->andReturn($projects);

        $mockPersistProjects = Mockery::mock(PersistProjects::class);
        $mockPersistProjects->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg instanceof Collection && $arg->count() === 0;
            }));

        $mockRepository = Mockery::mock(ProjectRepository::class);
        $mockRepository->shouldReceive('findNotInProjectIds')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg instanceof Collection && $arg->isEmpty();
            }))
            ->andReturn($existingProjects);

        $mockRepository->shouldReceive('delete')
            ->twice()
            ->with(Mockery::type(Project::class));

        $useCase = new SyncProjectsService(
            $mockGetProjects,
            $mockPersistProjects,
            $mockRepository
        );

        $result = $useCase->execute();

        expect($result)->toBeInstanceOf(SyncResult::class);
        expect($result->syncedCount)->toBe(0);
        expect($result->deletedCount)->toBe(2);
        expect($result->hasErrors)->toBeFalse();
    });

    test('外部APIから取得中にエラーが発生した場合、エラー情報を返す', function () {
        $mockGetProjects = Mockery::mock(GetProjects::class);
        $mockGetProjects->shouldReceive('execute')
            ->once()
            ->andThrow(new \Exception('External API error'));

        $mockPersistProjects = Mockery::mock(PersistProjects::class);
        $mockRepository = Mockery::mock(ProjectRepository::class);

        $useCase = new SyncProjectsService(
            $mockGetProjects,
            $mockPersistProjects,
            $mockRepository
        );

        $result = $useCase->execute();

        expect($result)->toBeInstanceOf(SyncResult::class);
        expect($result->syncedCount)->toBe(0);
        expect($result->deletedCount)->toBe(0);
        expect($result->hasErrors)->toBeTrue();
        expect($result->errorMessage)->toBe('External API error');
    });
});
