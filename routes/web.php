<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to the Breweries API, see the documentation at https://www.openbrewerydb.org/documentation',
    ]);
})->middleware('cache.headers:public;max_age=86400;etag');
