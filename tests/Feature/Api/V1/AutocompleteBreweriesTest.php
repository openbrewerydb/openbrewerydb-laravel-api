<?php

use Illuminate\Support\Facades\Log;

test('autocomplete returns a 307 redirect to search with a successor link', function () {
    $this->travelTo('2026-10-08 12:30:00');

    $response = $this->getJson('/v1/breweries/autocomplete?query=dog');

    $response->assertTemporaryRedirect()
        ->assertRedirect('/v1/breweries/search?query=dog')
        ->assertHeader('Link', '<https://localhost/v1/breweries/search?query=dog>; rel="successor-version"');
});

test('autocomplete logs a warning with caller details', function () {
    Log::spy();

    $this->withHeaders([
        'User-Agent' => 'BreweryFinder/1.0',
        'Origin' => 'https://breweryfinder.test',
        'Referer' => 'https://breweryfinder.test/search',
    ])->getJson('/v1/breweries/autocomplete?query=dog');

    Log::shouldHaveReceived('warning')->once()->with('Deprecated autocomplete endpoint called', [
        'query' => 'dog',
        'ip' => '127.0.0.1',
        'user_agent' => 'BreweryFinder/1.0',
        'origin' => 'https://breweryfinder.test',
        'referer' => 'https://breweryfinder.test/search',
        'accept' => 'application/json',
        'forwarded_for' => null,
    ]);
});

test('autocomplete returns 410 during a brownout window', function () {
    config(['platform.autocomplete_brownout' => true]);
    $this->travelTo('2026-10-08 12:05:00');

    $response = $this->getJson('/v1/breweries/autocomplete?query=dog');

    $response->assertGone()
        ->assertExactJson([
            'message' => 'The autocomplete endpoint has been removed. Use /v1/breweries/search instead.',
            'successor' => 'https://localhost/v1/breweries/search?query=dog',
        ])
        ->assertHeader('Link', '<https://localhost/v1/breweries/search?query=dog>; rel="successor-version"');
});

test('autocomplete redirects outside a brownout window', function () {
    config(['platform.autocomplete_brownout' => true]);
    $this->travelTo('2026-10-08 12:10:00');

    $response = $this->getJson('/v1/breweries/autocomplete?query=dog');

    $response->assertTemporaryRedirect();
});
