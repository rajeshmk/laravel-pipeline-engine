<?php

namespace Hatchyu\PipelineEngine;

use Illuminate\Support\ServiceProvider;
use Hatchyu\PipelineEngine\Console\InstallPipelineCommand;

class LaravelPipelineEngineServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallPipelineCommand::class,
            ]);

            // Register the stub for manual publishing if desired
            $this->publishes([
                __DIR__ . '/../stubs/ci.yml.stub' => base_path('.github/workflows/ci.yml'),
            ], 'pipeline-workflow');
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
