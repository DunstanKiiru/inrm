<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TPRServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge default package config (can be overridden in app/config)
        $this->mergeConfigFrom(
            __DIR__ . '/../config/inrm_tpr.php',
            'inrm_tpr'
        );
    }

    public function boot(): void
    {
        // Load package resources
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        if ($this->app->runningInConsole()) {
            // Register console commands
            $this->commands([
                \App\Console\TprRulesEvaluate::class,
                \App\Console\TprKriImportDaily::class,
                \App\Console\TprAssessmentsOverdue::class,
            ]);

            // Allow publishing config file
            $this->publishes([
                __DIR__ . '/../config/inrm_tpr.php' => config_path('inrm_tpr.php'),
            ], 'config');
        }
    }
}
