<?php

namespace Tests\Feature\Api\V1\GetBreweries;

use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

test('returns breweries filtered by city', function () {
    // Create breweries with different cities
    createBreweries(5, [
        'city' => 'San Diego',
    ]);
    createBreweries(5, [
        'city' => 'San Antonio',
    ]);

    // Filter by specific city
    $response = $this->getJson('/v1/breweries?by_city=San+Diego');

    // Assert only matching cities
    $response->assertOk()
        ->assertJsonCount(5);
    $cities = collect($response->json())->pluck('city');
    expect($cities->contains('San Antonio'))->toBeFalse();
    expect($cities->contains('San Diego'))->toBeTrue();
});

test('handles %20 as space in city filter', function () {
    // Create brewery in "San Diego"
    createBrewery(['city' => 'San Diego']);

    // Test with %20
    $response = $this->getJson('/v1/breweries?by_city=San%20Diego');
    $response->assertOk()
        ->assertJsonCount(1);
});
