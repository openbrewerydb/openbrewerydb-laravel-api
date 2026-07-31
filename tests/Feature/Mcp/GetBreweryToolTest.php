<?php

use App\Mcp\Servers\OpenBreweryDbServer;
use App\Mcp\Tools\GetBreweryTool;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;

test('gets a brewery by UUID', function () {
    $brewery = createBrewery([
        'name' => 'Breakside Brewery',
        'brewery_type' => 'micro',
    ]);

    OpenBreweryDbServer::tool(GetBreweryTool::class, [
        'id' => $brewery->id,
    ])->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->where('brewery.id', $brewery->id)
            ->where('brewery.name', 'Breakside Brewery')
            ->where('brewery.brewery_type', 'micro')
            ->has('brewery.address_1')
            ->has('brewery.state')
            ->has('brewery.street')
            ->etc());
});

test('returns an MCP error when a brewery does not exist', function () {
    $id = (string) Str::uuid();

    OpenBreweryDbServer::tool(GetBreweryTool::class, ['id' => $id])
        ->assertHasErrors(["No brewery was found with ID [{$id}]."]);
});

test('validates the brewery ID', function () {
    OpenBreweryDbServer::tool(GetBreweryTool::class, ['id' => 'not-a-uuid'])
        ->assertHasErrors(['must be a valid UUID']);
});
