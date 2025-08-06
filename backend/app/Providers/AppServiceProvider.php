<?php

namespace App\Providers;

use App\Usecase\WorkflowUsecase;
use App\Domain\Repositories\NodeRepositoryInterface;
use App\Domain\Repositories\WorkflowRepositoryInterface;
use App\Infrastructure\Repositories\NodeRepository;
use App\Infrastructure\Repositories\WorkflowRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // リポジトリの依存性注入
        $this->app->bind(WorkflowRepositoryInterface::class, WorkflowRepository::class);
        $this->app->bind(NodeRepositoryInterface::class, NodeRepository::class);

        // ユースケースの依存性注入
        $this->app->bind(WorkflowUsecase::class, function ($app) {
            return new WorkflowUsecase(
                $app->make(WorkflowRepositoryInterface::class),
                $app->make(NodeRepositoryInterface::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
