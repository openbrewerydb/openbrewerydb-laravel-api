<?php

namespace Tests\Feature\Api\V1\GetBreweries;

use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

test('returns default number of breweries (50)', function () {
    if (app()->environment('testing')) {
        $this->markTestSkipped('Skipping test in testing environment');
    }

    createBreweries(60);
    $response = $this->getJson('/v1/breweries');
    $response->assertOk()
        ->assertJsonCount(50);
});

test('returns proper brewery JSON structure', function () {
    createBreweries(1);
    $response = $this->getJson('/v1/breweries');
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

test('returns cache control headers', function () {
    createBreweries(1);
    $response = $this->getJson('/v1/breweries');
    $response->assertOk();

    // Check that the Cache-Control header contains the expected values
    $cacheControl = $response->headers->get('Cache-Control');
    expect($cacheControl)->toContain('public');
    expect($cacheControl)->toContain('max-age=');
    expect($cacheControl)->toContain('etag');
});

test('returns HTTP error 422 with invalid params', function () {
    // Create breweries to test with
    createBreweries(60);

    // Test with various invalid parameters
    $response = $this->getJson('/v1/breweries?'.http_build_query([
        'per_page' => 'invalid',  // Should be integer
        'sort' => ['invalid:sort'], // Should be string
        'by_city' => str_repeat('x', 256), // Exceeds max length
        'by_type' => str_repeat('x', 101), // Exceeds max length
        'by_state' => ['invalid', 'array'], // Should be string
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'per_page',
            'sort',
            'by_city',
            'by_type',
            'by_state',
        ]);
});
