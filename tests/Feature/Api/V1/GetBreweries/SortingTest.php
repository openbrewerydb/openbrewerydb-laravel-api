<?php

test('breweries can be sorted by name ascending', function () {
    createBrewery(['name' => 'Zebra Brewing']);
    createBrewery(['name' => 'Alpha Brewing']);
    createBrewery(['name' => 'Beta Brewing']);

    $response = $this->getJson('/v1/breweries?sort=name:asc');

    $response->assertOk();
    $breweries = collect($response->json());
    expect($breweries->pluck('name')->toArray())->toBe([
        'Alpha Brewing',
        'Beta Brewing',
        'Zebra Brewing',
    ]);
});

test('breweries can be sorted by name descending', function () {
    createBrewery(['name' => 'Zebra Brewing']);
    createBrewery(['name' => 'Alpha Brewing']);
    createBrewery(['name' => 'Beta Brewing']);

    $response = $this->getJson('/v1/breweries?sort=name:desc');

    $response->assertOk();
    $breweries = collect($response->json());
    expect($breweries->pluck('name')->toArray())->toBe([
        'Zebra Brewing',
        'Beta Brewing',
        'Alpha Brewing',
    ]);
});

test('breweries can be sorted by multiple fields', function () {
    createBrewery(['name' => 'Alpha Brewing', 'city' => 'Portland']);
    createBrewery(['name' => 'Alpha Brewing', 'city' => 'Seattle']);
    createBrewery(['name' => 'Beta Brewing', 'city' => 'Portland']);

    $response = $this->getJson('/v1/breweries?sort=name:asc,city:desc');

    $response->assertOk();
    $breweries = collect($response->json());
    $firstTwo = $breweries->take(2)->map(fn ($b) => ['name' => $b['name'], 'city' => $b['city']])->toArray();
    expect($firstTwo)->toBe([
        ['name' => 'Alpha Brewing', 'city' => 'Seattle'],
        ['name' => 'Alpha Brewing', 'city' => 'Portland'],
    ]);
});

test('breweries can be sorted by the documented type alias', function () {
    createBrewery(['name' => 'Zebra Brewing', 'brewery_type' => 'micro']);
    createBrewery(['name' => 'Alpha Brewing', 'brewery_type' => 'regional']);
    createBrewery(['name' => 'Beta Brewing', 'brewery_type' => 'brewpub']);

    $response = $this->getJson('/v1/breweries?sort=type,name:asc');

    $response->assertOk();
    $breweries = collect($response->json());
    expect($breweries->pluck('brewery_type')->toArray())->toBe([
        'brewpub',
        'micro',
        'regional',
    ]);
});

test('invalid brewery sorts return a validation error', function (string $sort) {
    $response = $this->getJson('/v1/breweries?'.http_build_query(['sort' => $sort]));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('sort');
})->with([
    'invalid direction' => 'name:invalid',
    'invalid field' => 'created_at:asc',
    'missing field' => ':asc',
    'missing direction' => 'name:',
    'too many separators' => 'name:asc:desc',
    'trailing separator' => 'name:asc,',
]);
