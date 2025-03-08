<?php

namespace Tests\Feature\Api\V1\GetBreweries;

use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

test('returns breweries filtered by name', function () {
    // Create breweries with different names
    createBreweries(5, [
        'name' => 'test brewery',
    ]);
    createBreweries(5, [
        'name' => 'another brewery',
    ]);

    // Filter by specific name
    $response = $this->getJson('/v1/breweries?by_name=test');

    // Assert only matching names
    $response->assertOk()
        ->assertJsonCount(5);
    $names = collect($response->json())->pluck('name');
    expect($names->contains('another brewery'))->toBeFalse();
    expect($names->contains('test brewery'))->toBeTrue();
});

test('handles plus (+) as space in name filter', function () {
    // Create brewery with spaces in name
    createBrewery(['name' => 'test brewery']);

    // Test with +, %20, and actual space
    $response = $this->getJson('/v1/breweries?by_name=test+brewery');
    $response->assertOk()
        ->assertJsonCount(1);
});
