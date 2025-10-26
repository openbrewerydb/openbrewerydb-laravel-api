<?php

test('breweries can be filtered by distance', function () {
    // Portland brewery
    createBrewery([
        'name' => 'Portland Brewing',
        'latitude' => '45.5155',
        'longitude' => '-122.6789',
    ]);

    // Seattle brewery
    createBrewery([
        'name' => 'Seattle Brewing',
        'latitude' => '47.6062',
        'longitude' => '-122.3321',
    ]);

    // Portland coordinates
    $response = $this->getJson('/v1/breweries?by_dist=45.5155,-122.6789');

    $response->assertOk();
    $breweries = collect($response->json());
    expect($breweries->first()['name'])->toBe('Portland Brewing');
});

test('distance filter validates coordinates format', function () {
    // Test invalid formats with expected error messages
    $invalidFormats = [
        'invalid',
        '45.5155',
        '91,-122.6789',
        '-91,-122.6789',
        '45.5155,-181',
        '45.5155,181',
        '90.1,-122.6789',
        '45.5155,180.1',
        'abc,def',
        '45.5155,',
        ',45.5155',
        '45.5155:122.6789',
    ];

    foreach ($invalidFormats as $format) {
        $response = $this->getJson('/v1/breweries?by_dist='.$format);
        $response->assertStatus(422)
            ->assertJsonValidationErrors('by_dist');
    }
});

test('distance filter accepts valid coordinate edge cases', function () {
    createBrewery([
        'name' => 'Test Brewery',
        'latitude' => '0',
        'longitude' => '0',
    ]);

    // Test valid edge cases
    $validFormats = [
        '0,0',                      // Zero coordinates
        '90,180',                    // Maximum values
        '-90,-180',                  // Minimum values
        '45.5155,-122.6789',         // Decimal values
        '90.0,180.0',               // Maximum values with decimal
        '-90.0,-180.0',             // Minimum values with decimal
        '89.999999,179.999999',     // Near maximum values
        '-89.999999,-179.999999',    // Near minimum values
    ];

    foreach ($validFormats as $format) {
        $response = $this->getJson('/v1/breweries?by_dist='.$format);
        $response->assertOk();
    }
});

test('distance filter respects radius and unit', function () {
    // Portland brewery
    createBrewery([
        'name' => 'Portland Brewing',
        'latitude' => '45.5155',
        'longitude' => '-122.6789',
    ]);

    // Seattle brewery
    createBrewery([
        'name' => 'Seattle Brewing',
        'latitude' => '47.6062',
        'longitude' => '-122.3321',
    ]);

    // Portland coordinates with 50 km radius
    $response = $this->getJson('/v1/breweries?by_dist=45.5155,-122.6789&by_dist_radius=50&by_dist_unit=km');

    $response->assertOk();
    $breweries = collect($response->json());
    expect($breweries)->toHaveCount(1)
        ->and($breweries->first()['name'])->toBe('Portland Brewing');

    // Portland coordinates with 300 km radius
    $response = $this->getJson('/v1/breweries?by_dist=45.5155,-122.6789&by_dist_radius=300&by_dist_unit=km');

    $response->assertOk();
    $breweries = collect($response->json());
    expect($breweries)->toHaveCount(2);
});

test('distance filter with different radius values', function () {
    // Portland brewery
    createBrewery([
        'name' => 'Portland Brewing',
        'latitude' => '45.5155',
        'longitude' => '-122.6789',
    ]);

    // Seattle brewery
    createBrewery([
        'name' => 'Seattle Brewing',
        'latitude' => '47.6062',
        'longitude' => '-122.3321',
    ]);

    // Portland coordinates with 100 km radius
    $response = $this->getJson('/v1/breweries?by_dist=45.5155,-122.6789&by_dist_radius=100&by_dist_unit=km');

    $response->assertOk();
    $breweries = collect($response->json());
    expect($breweries)->toHaveCount(1)
        ->and($breweries->first()['name'])->toBe('Portland Brewing');

    // Portland coordinates with 300 km radius
    $response = $this->getJson('/v1/breweries?by_dist=45.5155,-122.6789&by_dist_radius=300&by_dist_unit=km');

    $response->assertOk();
    $breweries = collect($response->json());
    expect($breweries)->toHaveCount(2);
});

test('distance filter with miles unit', function () {
    // Portland brewery
    createBrewery([
        'name' => 'Portland Brewing',
        'latitude' => '45.5155',
        'longitude' => '-122.6789',
    ]);

    // Seattle brewery
    createBrewery([
        'name' => 'Seattle Brewing',
        'latitude' => '47.6062',
        'longitude' => '-122.3321',
    ]);

    // Portland coordinates with 50 miles radius
    $response = $this->getJson('/v1/breweries?by_dist=45.5155,-122.6789&by_dist_radius=50&by_dist_unit=mi');

    $response->assertOk();
    $breweries = collect($response->json());
    expect($breweries)->toHaveCount(1)
        ->and($breweries->first()['name'])->toBe('Portland Brewing');

    // Portland coordinates with 200 miles radius
    $response = $this->getJson('/v1/breweries?by_dist=45.5155,-122.6789&by_dist_radius=200&by_dist_unit=mi');

    $response->assertOk();
    $breweries = collect($response->json());
    expect($breweries)->toHaveCount(2);
});

test('distance filter with radius and default unit', function () {
    // Portland brewery
    createBrewery([
        'name' => 'Portland Brewing',
        'latitude' => '45.5155',
        'longitude' => '-122.6789',
    ]);

    // Seattle brewery
    createBrewery([
        'name' => 'Seattle Brewing',
        'latitude' => '47.6062',
        'longitude' => '-122.3321',
    ]);

    // Portland coordinates with 50 miles radius (default unit)
    $response = $this->getJson('/v1/breweries?by_dist=45.5155,-122.6789&by_dist_radius=50');

    $response->assertOk();
    $breweries = collect($response->json());
    expect($breweries)->toHaveCount(1)
        ->and($breweries->first()['name'])->toBe('Portland Brewing');

    // Portland coordinates with 200 miles radius (default unit)
    $response = $this->getJson('/v1/breweries?by_dist=45.5155,-122.6789&by_dist_radius=200');

    $response->assertOk();
    $breweries = collect($response->json());
    expect($breweries)->toHaveCount(2);
});

test('distance filter works with other filters', function () {
    createBrewery([
        'name' => 'Portland Micro',
        'brewery_type' => 'micro',
        'latitude' => '45.5155',
        'longitude' => '-122.6789',
    ]);

    createBrewery([
        'name' => 'Portland Pub',
        'brewery_type' => 'brewpub',
        'latitude' => '45.5155',
        'longitude' => '-122.6789',
    ]);

    $response = $this->getJson('/v1/breweries?by_dist=45.5155,-122.6789&by_type=micro');

    $response->assertOk();
    $breweries = collect($response->json());
    expect($breweries)->toHaveCount(1)
        ->and($breweries->first()['name'])->toBe('Portland Micro');
});
