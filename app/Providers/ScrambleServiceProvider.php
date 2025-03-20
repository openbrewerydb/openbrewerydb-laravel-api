<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ScrambleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Scramble::registerApi('v1', ['info' => ['version' => '1.1.0']])
            ->expose(
                ui: '/docs/v1/api',
                document: '/docs/v1/openapi.json',
            )
            ->routes(function (Route $route) {
                return Str::startsWith($route->uri, 'v1/');
            });

        Gate::define('viewApiDocs', function () {
            return true;
        });
    }
}
