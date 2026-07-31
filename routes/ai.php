<?php

use App\Http\Middleware\EnsureMcpServerIsEnabled;
use App\Mcp\Servers\OpenBreweryDbServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', OpenBreweryDbServer::class)
    ->middleware([
        EnsureMcpServerIsEnabled::class,
        'throttle:mcp',
    ]);
