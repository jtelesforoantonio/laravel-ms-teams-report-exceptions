<?php

namespace JTelesforoAntonio\LaravelMsTeamsReportExceptions;

use Illuminate\Support\ServiceProvider;

class LaravelMsTeamsReportExceptionsProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ms-teams-report-exceptions.php', 'ms-teams-report-exceptions');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPublishing();
        if (!config('ms-teams-report-exceptions.enabled') || !config('ms-teams-report-exceptions.webhook_url')) {
            return;
        }
        $handler = $this->app->make(ReportException::class);
        $handler->register($this->app);
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    private function registerPublishing()
    {
        $this->publishes([
            __DIR__ . '/../config/ms-teams-report-exceptions.php' => config_path('ms-teams-report-exceptions.php'),
        ], 'ms-teams-report-exceptions-config');
    }
}