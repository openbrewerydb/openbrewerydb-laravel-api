<?php

use App\Enums\BreweryType;
use App\Mcp\Servers\OpenBreweryDbServer;
use App\Mcp\Tools\GetBreweryMetadataTool;
use Illuminate\Testing\Fluent\AssertableJson;

test('returns filtered brewery metadata', function () {
    createBreweries(2, [
        'state_province' => 'Oregon',
        'country' => 'United States',
        'brewery_type' => BreweryType::Micro,
    ]);
    createBrewery([
        'state_province' => 'Oregon',
        'country' => 'United States',
        'brewery_type' => BreweryType::Brewpub,
    ]);
    createBrewery([
        'state_province' => 'Washington',
        'country' => 'United States',
        'brewery_type' => BreweryType::Micro,
    ]);

    OpenBreweryDbServer::tool(GetBreweryMetadataTool::class, [
        'state' => 'Oregon',
        'types' => ['micro'],
    ])->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->where('total', 2)
            ->where('by_state.Oregon', 2)
            ->where('by_country.United States', 2)
            ->where('by_type.micro', 2));
});

test('returns empty metadata groups when no breweries match', function () {
    createBrewery(['state_province' => 'Oregon']);

    OpenBreweryDbServer::tool(GetBreweryMetadataTool::class, [
        'state' => 'California',
    ])->assertOk()
        ->assertSee([
            '"by_state":{}',
            '"by_country":{}',
            '"by_type":{}',
        ])
        ->assertStructuredContent([
            'total' => 0,
            'by_state' => [],
            'by_country' => [],
            'by_type' => [],
        ]);
});

test('aggregates metadata within a distance radius', function () {
    createBrewery([
        'state_province' => 'Oregon',
        'latitude' => '45.5155',
        'longitude' => '-122.6789',
    ]);
    createBrewery([
        'state_province' => 'Washington',
        'latitude' => '47.6062',
        'longitude' => '-122.3321',
    ]);

    OpenBreweryDbServer::tool(GetBreweryMetadataTool::class, [
        'latitude' => 45.5155,
        'longitude' => -122.6789,
        'radius' => 50,
        'distance_unit' => 'km',
    ])->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->where('total', 1)
            ->where('by_state.Oregon', 1)
            ->has('by_country', 1)
            ->has('by_type', 1));
});

test('validates metadata filters', function () {
    OpenBreweryDbServer::tool(GetBreweryMetadataTool::class, [
        'types' => ['restaurant'],
    ])->assertHasErrors(['selected types.0 is invalid']);
});

test('requires a radius when metadata coordinates are provided', function () {
    OpenBreweryDbServer::tool(GetBreweryMetadataTool::class, [
        'latitude' => 45.5155,
        'longitude' => -122.6789,
    ])->assertHasErrors(['Provide a radius when filtering metadata by coordinates']);
});
