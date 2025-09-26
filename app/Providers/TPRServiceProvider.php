<?php
namespace Inrm\TPR;
use Illuminate\Support\ServiceProvider;

class TPRServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/inrm_tpr.php', 'inrm_tpr');
    }
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\TprKriImportDaily::class,
                \App\Console\TprAssessmentsOverdue::class
            ]);
            $this->publishes([
                __DIR__.'/../config/inrm_tpr.php' => config_path('inrm_tpr.php'),
            ], 'config');
        }
    }
}
