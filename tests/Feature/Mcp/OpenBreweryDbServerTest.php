<?php

use App\Mcp\Servers\OpenBreweryDbServer;
use App\Mcp\Tools\ListBreweriesTool;
use Illuminate\Testing\Fluent\AssertableJson;

test('API root advertises documentation and MCP URLs', function () {
    $this->getJson('/')
        ->assertOk()
        ->assertJson([
            'message' => 'Welcome to the Open Brewery DB API.',
            'documentation_url' => url('/docs'),
            'mcp_url' => url('/mcp'),
        ]);
});

test('server initializes over the public HTTP endpoint', function () {
    $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2025-11-25',
            'capabilities' => [],
            'clientInfo' => ['name' => 'Pest', 'version' => '1.0.0'],
        ],
    ])->assertOk()
        ->assertHeader('MCP-Session-Id')
        ->assertJsonPath('result.serverInfo.name', 'Open Brewery DB')
        ->assertJsonPath('result.serverInfo.version', '1.0.0')
        ->assertJsonPath('result.capabilities.tools.listChanged', false);
});

test('public MCP endpoint can be disabled', function () {
    config(['platform.mcp_enabled' => false]);

    $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [],
    ])->assertServiceUnavailable()
        ->assertJsonPath('message', 'The MCP server is currently disabled.');
});

test('server exposes four read-only tools with structured schemas', function () {
    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/list',
        'params' => [],
    ]);

    $response->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('jsonrpc', '2.0')
            ->where('id', 1)
            ->has('result.tools', 4)
            ->where('result.tools.0.name', 'list-breweries')
            ->where('result.tools.0.annotations.readOnlyHint', true)
            ->where('result.tools.0.annotations.destructiveHint', false)
            ->where('result.tools.0.annotations.idempotentHint', true)
            ->where('result.tools.0.annotations.openWorldHint', false)
            ->where('result.tools.0.inputSchema.properties.per_page.default', 10)
            ->where('result.tools.0.outputSchema.properties.breweries.type', 'array')
            ->where('result.tools.1.name', 'get-brewery')
            ->where('result.tools.2.name', 'search-breweries')
            ->where('result.tools.3.name', 'get-brewery-metadata')
            ->etc());
});

test('tool metadata is descriptive', function () {
    OpenBreweryDbServer::tool(ListBreweriesTool::class)
        ->assertOk()
        ->assertName('list-breweries')
        ->assertTitle('List Breweries Tool')
        ->assertDescription('Browse the Open Brewery DB dataset using location, type, name, ID, and distance filters. Results are paginated and may be sorted by an allow-listed field.');
});
