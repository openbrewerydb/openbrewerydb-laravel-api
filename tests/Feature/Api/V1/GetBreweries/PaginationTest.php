<?php

namespace Tests\Feature\Api\V1\GetBreweries;

use App\Models\Brewery;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

test('returns another page of breweries', function () {
    // Create 60 breweries
    $breweries = Brewery::factory()->count(60)->create();

    // Get first page
    $firstPage = $this->getJson('/v1/breweries')
        ->assertOk()
        ->assertJsonCount(50)
        ->json();

    // Get second page
    $secondPage = $this->getJson('/v1/breweries?page=2')
        ->assertOk()
        ->assertJsonCount(10)
        ->json();

    // Assert different breweries
    $firstPageIds = collect($firstPage)->pluck('id');
    $secondPageIds = collect($secondPage)->pluck('id');

    // No IDs should be present in both pages
    expect($firstPageIds->intersect($secondPageIds)->isEmpty())->toBeTrue();

    // Second page should contain the last 10 breweries
    $lastTenBreweryIds = $breweries->sortBy('id')->take(-10)->pluck('id');
    expect($secondPageIds->diff($lastTenBreweryIds)->isEmpty())->toBeTrue();
});

test('returns limited number of breweries', function () {
    // Create 25 breweries
    Brewery::factory()->count(25)->create();

    // Request per_page=20
    $response = $this->getJson('/v1/breweries?per_page=20');

    $response->assertOk()
        ->assertJsonCount(20);
});

test('returns 422 if breweries exceed maximum per page', function () {
    Brewery::factory()->count(201)->create();
    $response = $this->getJson('/v1/breweries?per_page=501');
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['per_page']);
});
