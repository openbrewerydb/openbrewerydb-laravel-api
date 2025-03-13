<?php

namespace Tests\Feature\Api\V1\GetBreweries;

use App\Models\Brewery;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

test('returns different breweries for different pages', function () {
    // Create 75 breweries
    Brewery::factory()->count(50)->create();

    // Get results from page 1 with 25 per page
    $pageOne = $this->getJson('/v1/breweries?per_page=25')
        ->assertOk()
        ->assertJsonCount(25)
        ->json();

    // Get results from page 2 with 25 per page
    $pageTwo = $this->getJson('/v1/breweries?per_page=25&page=2')
        ->assertOk()
        ->assertJsonCount(25)
        ->json();

    // Extract IDs from each page
    $pageOneIds = collect($pageOne)->pluck('id');
    $pageTwoIds = collect($pageTwo)->pluck('id');

    // Ensure no overlap between pages
    expect($pageOneIds->intersect($pageTwoIds)->isEmpty())->toBeTrue();
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
