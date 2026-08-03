<?php

use App\Enums\BreweryType;
use App\Mcp\Servers\OpenBreweryDbServer;
use App\Mcp\Tools\ListBreweriesTool;
use Illuminate\Testing\Fluent\AssertableJson;

test('lists filtered breweries with pagination and sorting', function () {
    createBrewery([
        'name' => 'Zebra Brewing',
        'city' => 'Portland',
        'state_province' => 'Oregon',
        'brewery_type' => BreweryType::Micro,
    ]);
    createBrewery([
        'name' => 'Alpha Brewing',
        'city' => 'Portland',
        'state_province' => 'Oregon',
        'brewery_type' => BreweryType::Micro,
    ]);
    createBrewery([
        'name' => 'Seattle Brewing',
        'city' => 'Seattle',
        'state_province' => 'Washington',
        'brewery_type' => BreweryType::Brewpub,
    ]);

    OpenBreweryDbServer::tool(ListBreweriesTool::class, [
        'city' => 'Portland',
        'types' => ['micro'],
        'sort_by' => 'name',
        'sort_order' => 'asc',
    ])->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->has('breweries', 2)
            ->where('breweries.0.name', 'Alpha Brewing')
            ->where('breweries.1.name', 'Zebra Brewing')
            ->where('pagination.page', 1)
            ->where('pagination.per_page', 10)
            ->where('pagination.total', 2)
            ->where('pagination.last_page', 1)
            ->where('pagination.has_more', false));
});

test('paginates brewery results with a maximum page size', function () {
    createBreweries(12);

    OpenBreweryDbServer::tool(ListBreweriesTool::class, [
        'per_page' => 5,
        'page' => 2,
    ])->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->has('breweries', 5)
            ->where('pagination.page', 2)
            ->where('pagination.per_page', 5)
            ->where('pagination.total', 12)
            ->where('pagination.last_page', 3)
            ->where('pagination.has_more', true));
});

test('maps typed IDs and distance inputs to brewery filters', function () {
    $portland = createBrewery([
        'name' => 'Portland Brewing',
        'latitude' => '45.5155',
        'longitude' => '-122.6789',
    ]);
    createBrewery([
        'name' => 'Seattle Brewing',
        'latitude' => '47.6062',
        'longitude' => '-122.3321',
    ]);

    OpenBreweryDbServer::tool(ListBreweriesTool::class, [
        'ids' => [$portland->id],
        'latitude' => 45.5155,
        'longitude' => -122.6789,
        'radius' => 50,
        'distance_unit' => 'km',
    ])->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->has('breweries', 1)
            ->where('breweries.0.id', $portland->id)
            ->has('pagination'));
});

test('preserves literal filter characters', function () {
    createBrewery(['name' => 'C++ Brewing']);
    createBrewery(['name' => 'C Brewing']);

    OpenBreweryDbServer::tool(ListBreweriesTool::class, [
        'name' => 'C++',
    ])->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->has('breweries', 1)
            ->where('breweries.0.name', 'C++ Brewing')
            ->has('pagination'));
});

test('rejects invalid list arguments', function (array $arguments, string $message) {
    OpenBreweryDbServer::tool(ListBreweriesTool::class, $arguments)
        ->assertHasErrors([$message]);
})->with([
    'oversized page' => [['per_page' => 51], 'between 1 and 50'],
    'unknown type' => [['types' => ['restaurant']], 'selected types.0 is invalid'],
    'invalid sort' => [['sort_by' => 'website_url'], 'Sort by one of'],
    'missing longitude' => [['latitude' => 45.5], 'longitude field is required'],
]);
