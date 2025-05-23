<?php

use App\Enums\BreweryType;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

test('returns total breweries with default pagination values', function () {
    createBreweries(5);

    $response = $this->getJson('/v1/breweries/meta');

    $this->assertJsonApiResponse($response)
        ->assertOk()
        ->assertJsonPath('total', 5)
        ->assertJsonPath('page', 1)
        ->assertJsonPath('per_page', 50);
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

test('filters meta data by country', function () {
    createBreweries(3, [
        'country' => 'United States',
        'state_province' => 'California',
    ]);
    createBreweries(2, [
        'country' => 'Canada',
        'state_province' => 'British Columbia',
    ]);

    $response = $this->getJson('/v1/breweries/meta?by_country=Canada');

    $this->assertJsonApiResponse($response)
        ->assertOk()
        ->assertJsonPath('total', 2)
        ->assertJsonPath('by_state.British Columbia', 2)
        ->assertJsonMissingPath('by_state.California');
});

test('filters meta data by postal code', function () {
    createBreweries(3, [
        'postal_code' => '94107',
        'state_province' => 'California',
        'brewery_type' => BreweryType::Micro,
    ]);
    createBreweries(2, [
        'postal_code' => '97214',
        'state_province' => 'Oregon',
        'brewery_type' => BreweryType::Brewpub,
    ]);

    $response = $this->getJson('/v1/breweries/meta?by_postal=94107');

    $this->assertJsonApiResponse($response)
        ->assertOk()
        ->assertJsonPath('total', 3)
        ->assertJsonPath('by_state.California', 3)
        ->assertJsonPath('by_type.micro', 3)
        ->assertJsonMissingPath('by_state.Oregon')
        ->assertJsonMissingPath('by_type.brewpub');
});

test('filters meta data by specific brewery IDs', function () {
    $breweries1 = createBreweries(2, [
        'state_province' => 'California',
        'brewery_type' => BreweryType::Micro,
    ]);
    $breweries2 = createBreweries(3, [
        'state_province' => 'Oregon',
        'brewery_type' => BreweryType::Brewpub,
    ]);

    // Get IDs from the first set of breweries
    $ids = $breweries1->pluck('id')->join(',');

    $response = $this->getJson("/v1/breweries/meta?by_ids={$ids}");

    $this->assertJsonApiResponse($response)
        ->assertOk()
        ->assertJsonPath('total', 2)
        ->assertJsonPath('by_state.California', 2)
        ->assertJsonPath('by_type.micro', 2)
        ->assertJsonMissingPath('by_state.Oregon')
        ->assertJsonMissingPath('by_type.brewpub');
});

test('filters meta data by excluding specific brewery types', function () {
    createBreweries(3, ['brewery_type' => BreweryType::Micro]);
    createBreweries(2, ['brewery_type' => BreweryType::Brewpub]);
    createBreweries(4, ['brewery_type' => BreweryType::Regional]);

    $response = $this->getJson('/v1/breweries/meta?exclude_types=micro,brewpub');

    $this->assertJsonApiResponse($response)
        ->assertOk()
        ->assertJsonPath('total', 4)
        ->assertJsonPath('by_type.regional', 4)
        ->assertJsonMissingPath('by_type.micro')
        ->assertJsonMissingPath('by_type.brewpub');
});

test('returns custom page and per_page values when provided', function () {
    createBreweries(20);

    $response = $this->getJson('/v1/breweries/meta?page=2&per_page=10');

    $this->assertJsonApiResponse($response)
        ->assertOk()
        ->assertJsonPath('page', 2)
        ->assertJsonPath('per_page', 10);
});

test('validates page and per_page parameters', function () {
    createBreweries(5);

    $response = $this->getJson('/v1/breweries/meta?page=0&per_page=0');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['page', 'per_page']);
});

test('meta endpoint returns cache control headers', function () {
    createBreweries(3);

    $response = $this->getJson('/v1/breweries/meta');

    $response->assertOk();

    // Check that the Cache-Control header contains the expected values
    $cacheControl = $response->headers->get('Cache-Control');
    expect($cacheControl)->toContain('public');
    expect($cacheControl)->toContain('max-age=');
    expect($cacheControl)->toContain('etag');
});
