<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetBreweryMetadataTool;
use App\Mcp\Tools\GetBreweryTool;
use App\Mcp\Tools\ListBreweriesTool;
use App\Mcp\Tools\SearchBreweriesTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Open Brewery DB')]
#[Version('1.0.0')]
#[Instructions('Use these read-only tools to find breweries and explore aggregate brewery data from the public Open Brewery DB dataset. Paginate list and search requests instead of requesting more data than needed.')]
class OpenBreweryDbServer extends Server
{
    /** @var array<int, class-string<\Laravel\Mcp\Server\Tool>> */
    protected array $tools = [
        ListBreweriesTool::class,
        GetBreweryTool::class,
        SearchBreweriesTool::class,
        GetBreweryMetadataTool::class,
    ];

    /** @var array<int, class-string<\Laravel\Mcp\Server\Resource>> */
    protected array $resources = [
    ];

    /** @var array<int, class-string<\Laravel\Mcp\Server\Prompt>> */
    protected array $prompts = [
    ];
}
