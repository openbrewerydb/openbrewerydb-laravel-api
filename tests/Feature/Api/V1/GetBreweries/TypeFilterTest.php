<?php

namespace Tests\Feature\Api\V1\GetBreweries;

use App\Enums\BreweryType;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

test('returns breweries filtered by type', function () {
    // Create breweries of different types
    createBreweries(5, [
        'brewery_type' => BreweryType::Micro,
    ]);
    createBreweries(5, [
        'brewery_type' => BreweryType::Brewpub,
    ]);

    // Filter by specific type
    $response = $this->getJson('/v1/breweries?by_type=micro');

    // Assert only matching types
    $response->assertOk()
        ->assertJsonCount(5);
    $types = collect($response->json())->pluck('brewery_type');
    expect($types->contains('brewpub'))->toBeFalse();
    expect($types->contains('micro'))->toBeTrue();
});

test('returns validation error when an invalid brewery type is provided', function () {
    // Create breweries of different types
    createBreweries(5, [
        'brewery_type' => BreweryType::Micro,
    ]);
    createBreweries(5, [
        'brewery_type' => BreweryType::Brewpub,
    ]);

    // Filter by invalid type
    $response = $this->getJson('/v1/breweries?by_type=invalid');

    // Assert no matches
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['by_type']);
});

test('returns breweries filtered by multiple types', function () {
    // Create breweries of different types
    createBreweries(5, [
        'brewery_type' => BreweryType::Micro,
    ]);
    createBreweries(5, [
        'brewery_type' => BreweryType::Large,
    ]);
    createBreweries(5, [
        'brewery_type' => BreweryType::Brewpub,
    ]);

    // Filter by multiple types
    $response = $this->getJson('/v1/breweries?by_type=micro,large');

    // Assert only matching types
    $response->assertOk()
        ->assertJsonCount(10);
    $types = collect($response->json())->pluck('brewery_type');
    expect($types->contains('brewpub'))->toBeFalse();
    expect($types->contains('micro'))->toBeTrue();
    expect($types->contains('large'))->toBeTrue();
});
