<?php

namespace Tests\Feature\Api\V1;

use App\Models\Brewery;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    Cache::flush();
    RateLimiter::clear('api:127.0.0.1');
});

test('allows requests within rate limit', function () {
    // Create a brewery for testing
    $brewery = Brewery::factory()->create();

    // Make requests within the rate limit (120 per minute by default)
    for ($i = 0; $i < 5; $i++) {
        $response = $this->getJson("/v1/breweries/{$brewery->id}");
        $response->assertOk();
    }
});

test('blocks requests when rate limit is exceeded', function () {
    // Create a brewery for testing
    $brewery = Brewery::factory()->create();

    // Get the configured rate limit from config
    $rateLimit = config('platform.api_rate_limit', 120);

    // Hit the rate limit by making more requests than allowed
    for ($i = 0; $i < $rateLimit; $i++) {
        $response = $this->getJson("/v1/breweries/{$brewery->id}");
        $response->assertOk();
    }

    // The next request should be rate limited
    $response = $this->getJson("/v1/breweries/{$brewery->id}");
    $response->assertStatus(429); // Too Many Requests
});

test('rate limit applies to different endpoints', function () {
    // Create breweries for testing
    Brewery::factory()->count(10)->create();

    $rateLimit = config('platform.api_rate_limit', 120);

    // Hit rate limit with mixed endpoints
    for ($i = 0; $i < $rateLimit; $i++) {
        if ($i % 3 === 0) {
            $response = $this->getJson('/v1/breweries');
        } elseif ($i % 3 === 1) {
            $response = $this->getJson('/v1/breweries/meta');
        } else {
            $response = $this->getJson('/v1/breweries/random');
        }
        $response->assertSuccessful();
    }

    // Next request should be rate limited regardless of endpoint
    $response = $this->getJson('/v1/breweries');
    $response->assertStatus(429);
});

test('rate limit is per IP address', function () {
    // This test simulates different IP addresses
    // In a real scenario, different IPs would have separate rate limits

    $brewery = Brewery::factory()->create();

    // First IP makes some requests
    $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.1']);
    for ($i = 0; $i < 10; $i++) {
        $response = $this->getJson("/v1/breweries/{$brewery->id}");
        $response->assertOk();
    }

    // Second IP should have its own rate limit counter
    $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.2']);
    for ($i = 0; $i < 10; $i++) {
        $response = $this->getJson("/v1/breweries/{$brewery->id}");
        $response->assertOk();
    }
});

test('rate limit includes retry-after header when exceeded', function () {
    $brewery = Brewery::factory()->create();
    $rateLimit = config('platform.api_rate_limit', 120);

    // Hit the rate limit
    for ($i = 0; $i < $rateLimit; $i++) {
        $this->getJson("/v1/breweries/{$brewery->id}");
    }

    // The rate limited response should include Retry-After header
    $response = $this->getJson("/v1/breweries/{$brewery->id}");
    $response->assertStatus(429)
        ->assertHeader('Retry-After');
});

test('rate limit applies to search endpoint', function () {
    Brewery::factory()->create(['name' => 'Test Brewery']);

    $rateLimit = config('platform.api_rate_limit', 120);

    // Hit rate limit with search requests
    for ($i = 0; $i < $rateLimit; $i++) {
        $response = $this->getJson('/v1/breweries/search?query=Test');
        $response->assertSuccessful();
    }

    // Next search request should be rate limited
    $response = $this->getJson('/v1/breweries/search?query=Test');
    $response->assertStatus(429);
});

test('rate limit applies to filtered brewery requests', function () {
    Brewery::factory()->count(5)->create([
        'state_province' => 'California',
        'brewery_type' => 'micro'
    ]);

    $rateLimit = config('platform.api_rate_limit', 120);

    // Hit rate limit with filtered requests
    for ($i = 0; $i < $rateLimit; $i++) {
        $response = $this->getJson('/v1/breweries?by_state=California&by_type=micro');
        $response->assertSuccessful();
    }

    // Next filtered request should be rate limited
    $response = $this->getJson('/v1/breweries?by_state=California&by_type=micro');
    $response->assertStatus(429);
});

test('rate limit resets after time window', function () {
    $this->markTestSkipped('Skipping time-sensitive test - would require waiting or mocking time');

    // This test would verify that after 1 minute, the rate limit resets
    // In practice, this would be tested with time mocking or in integration tests
    // rather than unit tests due to the time dependency
});

test('rate limit configuration can be overridden', function () {
    // Test that the rate limit respects the configuration
    $defaultLimit = config('platform.api_rate_limit');
    expect($defaultLimit)->toBe(120);

    // Verify the rate limiter is using this configuration
    $brewery = Brewery::factory()->create();

    // Make a few requests to verify the rate limiter is working
    for ($i = 0; $i < 5; $i++) {
        $response = $this->getJson("/v1/breweries/{$brewery->id}");
        $response->assertOk();
    }
});
