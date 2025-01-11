<?php

namespace App\Providers;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\DB;
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
        if (DB::getSchemaBuilder()->hasTable('breweries')) {
            AboutCommand::add('Open Brewery DB', [
                'last_updated' => DB::table('breweries')->max('updated_at'),
                'records' => DB::table('breweries')->count(),
            ]);
        }
    }
}
