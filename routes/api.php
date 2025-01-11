<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/about', function () {
    return response()->json([
        'name' => 'Open Brewery DB API',
        'version' => '1.0.0',
        'data' => [
            'records' => DB::table('breweries')->count(),
            'last_updated' => DB::table('breweries')->max('updated_at'),
        ],
    ]);
});

require __DIR__.'/api/v1/routes.php';
