<?php

use App\Mcp\Servers\OpenBreweryDbServer;
use App\Mcp\Tools\SearchBreweriesTool;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    config(['scout.driver' => 'collection']);
});

test('searches breweries with deterministic pagination', function () {
    createBrewery(['name' => 'Special Brew Company']);
    createBrewery(['name' => 'Special Beer Factory']);
    createBrewery(['name' => 'Another Brewery']);

    OpenBreweryDbServer::tool(SearchBreweriesTool::class, [
        'query' => 'special',
        'per_page' => 1,
    ])->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->has('breweries', 1)
            ->where('pagination.page', 1)
            ->where('pagination.per_page', 1)
            ->where('pagination.has_more', true)
            ->where('pagination.next_page', 2));
});

test('returns an empty structured search result', function () {
    createBrewery(['name' => 'Known Brewery']);

    OpenBreweryDbServer::tool(SearchBreweriesTool::class, [
        'query' => 'missing brewery',
    ])->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->has('breweries', 0)
            ->where('pagination.page', 1)
            ->where('pagination.per_page', 10)
            ->where('pagination.has_more', false)
            ->where('pagination.next_page', null));
});

test('validates search arguments', function (array $arguments, string $message) {
    OpenBreweryDbServer::tool(SearchBreweriesTool::class, $arguments)
        ->assertHasErrors([$message]);
})->with([
    'missing query' => [[], 'Provide a brewery name or search phrase'],
    'short query' => [['query' => 'ab'], 'at least 3 characters'],
    'oversized page' => [['query' => 'brewery', 'per_page' => 51], 'between 1 and 50'],
]);
