<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to the Open Brewery DB API.',
        'documentation_url' => url('/docs'),
        'mcp_url' => url('/mcp'),
    ]);
})->middleware('cache.headers:public;max_age=86400;etag');
