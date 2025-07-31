<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->configureRateLimits();
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
     * Configure rate limits for route groups.
     */
    protected function configureRateLimits(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(
                maxAttempts: config('platform.api_rate_limit'),
            )->by($request->user()?->id ?: $request->ip());
        });
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
