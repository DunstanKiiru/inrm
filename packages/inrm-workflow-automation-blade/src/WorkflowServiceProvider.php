<?php
namespace Inrm\Workflow;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class WorkflowServiceProvider extends ServiceProvider
{
    public function register(): void {}
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'workflow');

        // Optionally auto-schedule the runner every 5 minutes if available
        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->call(function () {
                (new \Inrm\Workflow\Support\Engine())->runDue();
            })->everyFiveMinutes()->name('inrm:workflow:run-due')->withoutOverlapping();
        });
    }
}
