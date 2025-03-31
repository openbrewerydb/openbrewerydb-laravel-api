<?php

use App\Enums\BreweryType;
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

test('filters meta data by state', function () {
    createBreweries(3, ['state_province' => 'California']);
    createBreweries(2, ['state_province' => 'Oregon']);
    createBreweries(4, ['state_province' => 'Washington']);

    $response = $this->getJson('/v1/breweries/meta?by_state=California');

    $this->assertJsonApiResponse($response)
        ->assertOk()
        ->assertJsonPath('total', 3)
        ->assertJsonPath('by_state.California', 3)
        ->assertJsonMissingPath('by_state.Oregon')
        ->assertJsonMissingPath('by_state.Washington');
});

test('filters meta data by type', function () {
    createBreweries(3, ['brewery_type' => BreweryType::Micro]);
    createBreweries(2, ['brewery_type' => BreweryType::Brewpub]);
    createBreweries(4, ['brewery_type' => BreweryType::Regional]);

    $response = $this->getJson('/v1/breweries/meta?by_type=micro');

    $this->assertJsonApiResponse($response)
        ->assertOk()
        ->assertJsonPath('total', 3)
        ->assertJsonPath('by_type.micro', 3)
        ->assertJsonMissingPath('by_type.brewpub')
        ->assertJsonMissingPath('by_type.regional');
});

test('filters meta data by city', function () {
    createBreweries(3, ['city' => 'Portland', 'state_province' => 'Oregon']);
    createBreweries(2, ['city' => 'Seattle', 'state_province' => 'Washington']);
    createBreweries(4, ['city' => 'San Francisco', 'state_province' => 'California']);

    $response = $this->getJson('/v1/breweries/meta?by_city=Portland');

    $this->assertJsonApiResponse($response)
        ->assertOk()
        ->assertJsonPath('total', 3)
        ->assertJsonPath('by_state.Oregon', 3)
        ->assertJsonMissingPath('by_state.Washington')
        ->assertJsonMissingPath('by_state.California');
});

test('filters meta data by name', function () {
    createBreweries(3, [
        'name' => 'Awesome Brewing Co',
        'state_province' => 'California',
        'brewery_type' => BreweryType::Micro,
    ]);
    createBreweries(2, [
        'name' => 'Terrible Brewing Co',
        'state_province' => 'Oregon',
        'brewery_type' => BreweryType::Brewpub,
    ]);

    $response = $this->getJson('/v1/breweries/meta?by_name=Awesome');

    $this->assertJsonApiResponse($response)
        ->assertOk()
        ->assertJsonPath('total', 3)
        ->assertJsonPath('by_state.California', 3)
        ->assertJsonPath('by_type.micro', 3)
        ->assertJsonMissingPath('by_state.Oregon')
        ->assertJsonMissingPath('by_type.brewpub');
});

test('combines multiple filters for meta data', function () {
    // California breweries
    createBreweries(2, [
        'state_province' => 'California',
        'brewery_type' => BreweryType::Micro,
        'city' => 'San Francisco',
    ]);
    createBreweries(3, [
        'state_province' => 'California',
        'brewery_type' => BreweryType::Brewpub,
        'city' => 'Los Angeles',
    ]);

    // Oregon breweries
    createBreweries(4, [
        'state_province' => 'Oregon',
        'brewery_type' => BreweryType::Micro,
        'city' => 'Portland',
    ]);
    createBreweries(1, [
        'state_province' => 'Oregon',
        'brewery_type' => BreweryType::Regional,
        'city' => 'Eugene',
    ]);

    // Apply multiple filters: state=California and type=micro
    $response = $this->getJson('/v1/breweries/meta?by_state=California&by_type=micro');

    $this->assertJsonApiResponse($response)
        ->assertOk()
        ->assertJsonPath('total', 2)
        ->assertJsonPath('by_state.California', 2)
        ->assertJsonPath('by_type.micro', 2)
        ->assertJsonMissingPath('by_type.brewpub')
        ->assertJsonMissingPath('by_type.regional')
        ->assertJsonMissingPath('by_state.Oregon');
});

test('returns validation error when an invalid brewery type is provided', function () {
    createBreweries(5);

    $response = $this->getJson('/v1/breweries/meta?by_type=invalid');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['by_type']);
});
