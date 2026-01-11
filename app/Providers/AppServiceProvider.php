<?php

namespace App\Providers;

use App\Application\Contract\CollectCommits;
use App\Application\Contract\GetProjects;
use App\Application\Contract\PersistProjects;
use App\Application\Contract\SyncProjects;
use App\Application\Port\CommitRepository;
use App\Application\Port\GitApi;
use App\Application\Port\ProjectRepository;
use App\Application\Service\CollectCommits as CollectCommitsService;
use App\Application\Service\GetProjects as GetProjectsService;
use App\Application\Service\PersistProjects as PersistProjectsService;
use App\Application\Service\SyncProjects as SyncProjectsService;
use App\Infrastructure\GitLab\GitLabApiClient;
use App\Infrastructure\Repositories\EloquentCommitRepository;
use App\Infrastructure\Repositories\EloquentProjectRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // インターフェースと実装のバインディング
        $this->app->bind(GitApi::class, function ($app) {
            return GitLabApiClient::fromConfig();
        });
        $this->app->bind(ProjectRepository::class, EloquentProjectRepository::class);
        $this->app->bind(CommitRepository::class, EloquentCommitRepository::class);
        $this->app->bind(GetProjects::class, GetProjectsService::class);
        $this->app->bind(PersistProjects::class, PersistProjectsService::class);
        $this->app->bind(SyncProjects::class, SyncProjectsService::class);
        $this->app->bind(CollectCommits::class, CollectCommitsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
