<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

test('returns 400 when query parameter is missing', function () {
    $response = $this->getJson('/v1/breweries/search');

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['query']);
});

test('returns 400 when query parameter is empty', function () {
    $response = $this->getJson('/v1/breweries/search?query=');

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['query']);
});

test('returns empty array when no breweries match search query', function () {
    createBreweries(5);

    $response = $this->getJson('/v1/breweries/search?query=NonExistentBrewery');

    $response->assertOk()
        ->assertJsonCount(0);
});

test('returns matching breweries for name search', function () {
    if (app()->environment('testing')) {
        $this->markTestSkipped('Skipping test in testing environment');
    }

    // Create some breweries
    createBrewery(['name' => 'Special Brew Co']);
    createBrewery(['name' => 'Another Brewery']);
    createBrewery(['name' => 'Special Beer Factory']);

    $response = $this->getJson('/v1/breweries/search?query=Special');

    $response->assertOk()
        ->assertJsonCount(2)
        ->assertJsonFragment(['name' => 'Special Brew Co'])
        ->assertJsonFragment(['name' => 'Special Beer Factory']);
});

test('returns default number of results (50) when more matches exist', function () {
    if (app()->environment('testing')) {
        $this->markTestSkipped('Skipping test in testing environment');
    }

    // Create 60 breweries with similar names
    createBreweries(60, ['name' => 'Test Brewery']);

    $response = $this->getJson('/v1/breweries/search?query=Test');

    $response->assertOk()
        ->assertJsonCount(50);
});

test('respects per_page parameter', function () {
    // Create 30 breweries
    createBreweries(30, ['name' => 'Test Brewery']);

    $response = $this->getJson('/v1/breweries/search?query=Test&per_page=10');

    $response->assertOk()
        ->assertJsonCount(10);
});

test('handles special characters in search query', function () {
    if (app()->environment('testing')) {
        $this->markTestSkipped('Skipping test in testing environment');
    }

    createBrewery(['name' => "O'Brien's Pub & Brewery"]);
    createBrewery(['name' => 'Smith & Sons Brewing']);

    $response = $this->getJson('/v1/breweries/search?query='.urlencode("O'Brien"));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['name' => "O'Brien's Pub & Brewery"]);
});

test('returns correct json structure', function () {
    createBrewery([
        'name' => 'Test Brewery',
        'brewery_type' => 'micro',
        'city' => 'Portland',
        'state_province' => 'Oregon',
        'country' => 'United States',
    ]);

    $response = $this->getJson('/v1/breweries/search?query=Test');

    $response->assertOk()
        ->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'brewery_type',
                'address_1',
                'address_2',
                'address_3',
                'city',
                'state_province',
                'postal_code',
                'country',
                'longitude',
                'latitude',
                'phone',
                'website_url',
                'state',
                'street',
            ],
        ]);
});

test('search is case insensitive', function () {
    if (app()->environment('testing')) {
        $this->markTestSkipped('Skipping test in testing environment');
    }

    createBrewery(['name' => 'UPPERCASE BREWERY']);
    createBrewery(['name' => 'lowercase brewery']);
    createBrewery(['name' => 'MiXeD cAsE bReWeRy']);

    $response = $this->getJson('/v1/breweries/search?query=brewery');

    $response->assertOk()
        ->assertJsonCount(3);
});
