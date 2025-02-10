<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureModels();
        $this->forceHttps();
    }

    /**
     * Configure the application's models.
     */
    protected function configureModels(): void
    {
        Model::unguard();

        $this->app->isProduction() || Model::shouldBeStrict();
    }

    /**
     * Force https scheme in non-local environments.
     */
    protected function forceHttps(): void
    {
        if (app()->environment('local')) {
            return;
        }

        URL::forceScheme('https');
    }
}
