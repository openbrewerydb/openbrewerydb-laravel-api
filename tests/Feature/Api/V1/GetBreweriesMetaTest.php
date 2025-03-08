<?php

use App\Enums\BreweryType;
use App\Models\Brewery;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

test('returns total breweries', function () {
    createBreweries(5);

    $response = $this->getJson('/v1/breweries/meta');

    $this->assertJsonApiResponse($response)
        ->assertOk()
        ->assertJsonPath('total', 5);
});

test('returns total breweries by state', function () {
    createBreweries(3, ['state_province' => 'California']);
    createBreweries(2, ['state_province' => 'Oregon']);

    $response = $this->getJson('/v1/breweries/meta');

    $this->assertJsonApiResponse($response)
        ->assertOk()
        ->assertJsonPath('by_state.California', 3)
        ->assertJsonPath('by_state.Oregon', 2);
});

test('returns total breweries by type', function () {
    createBreweries(2, ['brewery_type' => BreweryType::Micro]);
    createBreweries(1, ['brewery_type' => BreweryType::Regional]);

    $response = $this->getJson('/v1/breweries/meta');

    $this->assertJsonApiResponse($response)
        ->assertOk()
        ->assertJsonPath('by_type.micro', 2)
        ->assertJsonPath('by_type.regional', 1);
});

test('handles utf8 characters in meta data', function () {
    createBreweries(2, ['state_province' => 'São Paulo']);

    $response = $this->getJson('/v1/breweries/meta');

    $this->assertJsonApiResponse($response)
        ->assertOk()
        ->assertJsonPath('by_state.São Paulo', 2);
});
