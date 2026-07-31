<?php

use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

test('rate limits public MCP requests by IP address', function () {
    config(['platform.mcp_rate_limit' => 2]);

    $payload = [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'ping',
        'params' => [],
    ];

    $this->withServerVariables(['REMOTE_ADDR' => '192.168.10.1']);
    $this->postJson('/mcp', $payload)->assertOk();
    $this->postJson('/mcp', $payload)->assertOk();
    $this->postJson('/mcp', $payload)
        ->assertTooManyRequests()
        ->assertHeader('Retry-After');

    $this->withServerVariables(['REMOTE_ADDR' => '192.168.10.2']);
    $this->postJson('/mcp', $payload)->assertOk();
});

test('uses a default limit of sixty requests per minute', function () {
    expect(config('platform.mcp_rate_limit'))->toBe(60);
});
