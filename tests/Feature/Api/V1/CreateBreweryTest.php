<?php

use App\Enums\BreweryType;
use Illuminate\Support\Facades\Cache;

/** @var \Tests\Feature\Api\ApiTestCase $this */
beforeEach(function () {
    Cache::flush();
});

test('can create a brewery with minimal required fields', function () {
    $payload = [
        'name' => 'Alchemist Pub and Brewery',
        'city' => 'Stowe',
        'state_province' => 'Vermont',
        'country' => 'United States',
    ];

    $response = $this->postJson('/v1/breweries', $payload);

    $this->assertJsonApiResponse($response)
        ->assertCreated()
        ->assertJsonPath('name', 'Alchemist Pub and Brewery')
        ->assertJsonPath('city', 'Stowe')
        ->assertJsonPath('state_province', 'Vermont')
        ->assertJsonPath('country', 'United States')
        ->assertJsonPath('brewery_type', BreweryType::Unknown->value);

    $id = $response->json('id');
    expect($id)->not->toBeEmpty();

    $response->assertHeader('Location', url("/v1/breweries/{$id}"));

    $this->assertDatabaseHas('breweries', [
        'id' => $id,
        'name' => 'Alchemist Pub and Brewery',
        'city' => 'Stowe',
        'brewery_type' => BreweryType::Unknown->value,
    ]);
});

test('can create a brewery with all fields', function () {
    $payload = [
        'name' => 'Dogfish Head Brewery',
        'brewery_type' => 'micro',
        'address_1' => '6 Cannery Village Center',
        'address_2' => 'Suite 100',
        'address_3' => 'Building B',
        'city' => 'Milton',
        'state_province' => 'Delaware',
        'postal_code' => '19968',
        'country' => 'United States',
        'phone' => '3026841000',
        'website_url' => 'https://www.dogfish.com',
        'latitude' => 38.770154,
        'longitude' => -75.311745,
    ];

    $response = $this->postJson('/v1/breweries', $payload);

    $this->assertJsonApiResponse($response)
        ->assertCreated()
        ->assertJsonPath('name', $payload['name'])
        ->assertJsonPath('brewery_type', 'micro')
        ->assertJsonPath('address_1', $payload['address_1'])
        ->assertJsonPath('address_2', $payload['address_2'])
        ->assertJsonPath('address_3', $payload['address_3'])
        ->assertJsonPath('city', $payload['city'])
        ->assertJsonPath('state_province', $payload['state_province'])
        ->assertJsonPath('postal_code', $payload['postal_code'])
        ->assertJsonPath('country', $payload['country'])
        ->assertJsonPath('phone', $payload['phone'])
        ->assertJsonPath('website_url', $payload['website_url'])
        ->assertJsonPath('latitude', $payload['latitude'])
        ->assertJsonPath('longitude', $payload['longitude']);

    $this->assertJsonApiStructure($response);
});

test('maps unrecognized brewery type like distillery to unknown', function () {
    $payload = [
        'name' => 'Copper & Kings American Brandy Co',
        'brewery_type' => 'distillery',
        'city' => 'Louisville',
        'state_province' => 'Kentucky',
        'country' => 'United States',
    ];

    $response = $this->postJson('/v1/breweries', $payload);

    $this->assertJsonApiResponse($response)
        ->assertCreated()
        ->assertJsonPath('brewery_type', BreweryType::Unknown->value);

    $this->assertDatabaseHas('breweries', [
        'id' => $response->json('id'),
        'brewery_type' => BreweryType::Unknown->value,
    ]);
});

test('preserves recognized brewery types like cidery, brewpub, and micro', function () {
    $types = ['cidery', 'brewpub', 'micro', 'taproom'];

    foreach ($types as $type) {
        $payload = [
            'name' => "Sample {$type} Venue",
            'brewery_type' => $type,
            'city' => 'Seattle',
            'state_province' => 'Washington',
            'country' => 'United States',
        ];

        $response = $this->postJson('/v1/breweries', $payload);

        $response->assertCreated()
            ->assertJsonPath('brewery_type', $type);
    }
});

test('supports street and state alias fields', function () {
    $payload = [
        'name' => 'Alias Test Brewery',
        'street' => '789 Craft Blvd',
        'city' => 'Denver',
        'state' => 'Colorado',
        'country' => 'United States',
    ];

    $response = $this->postJson('/v1/breweries', $payload);

    $this->assertJsonApiResponse($response)
        ->assertCreated()
        ->assertJsonPath('address_1', '789 Craft Blvd')
        ->assertJsonPath('street', '789 Craft Blvd')
        ->assertJsonPath('state_province', 'Colorado')
        ->assertJsonPath('state', 'Colorado');
});

test('validates required fields', function () {
    $response = $this->postJson('/v1/breweries', []);

    $this->assertJsonApiResponse($response)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'city', 'state_province', 'country']);
});

test('validates website_url format', function () {
    $response = $this->postJson('/v1/breweries', [
        'name' => 'Bad URL Brewery',
        'city' => 'Austin',
        'state_province' => 'Texas',
        'country' => 'United States',
        'website_url' => 'not-a-valid-url',
    ]);

    $this->assertJsonApiResponse($response)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['website_url']);
});

test('validates latitude and longitude ranges', function () {
    $response = $this->postJson('/v1/breweries', [
        'name' => 'Out of bounds Brewery',
        'city' => 'Austin',
        'state_province' => 'Texas',
        'country' => 'United States',
        'latitude' => 150.0,
        'longitude' => -200.0,
    ]);

    $this->assertJsonApiResponse($response)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['latitude', 'longitude']);
});
