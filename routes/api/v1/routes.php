<?php

use App\Http\Controllers\Api\V1\AutocompleteBreweries;
use App\Http\Controllers\Api\V1\GetBreweriesMeta;
use App\Http\Controllers\Api\V1\GetBrewery;
use App\Http\Controllers\Api\V1\ListBreweries;
use App\Http\Controllers\Api\V1\RandomBrewery;
use App\Http\Controllers\Api\V1\SearchBreweries;
use Illuminate\Support\Facades\Route;

Route::name('v1.')->prefix('v1')->group(function () {
    Route::get('/breweries', ListBreweries::class)->name('breweries.index');
    Route::get('/breweries/autocomplete', AutocompleteBreweries::class)->name('breweries.autocomplete');
    Route::get('/breweries/meta', GetBreweriesMeta::class)->name('breweries.meta');
    Route::get('/breweries/random', RandomBrewery::class)->name('breweries.random');
    Route::get('/breweries/search', SearchBreweries::class)->name('breweries.search');
    Route::get('/breweries/{id}', GetBrewery::class)->name('breweries.show');
});
