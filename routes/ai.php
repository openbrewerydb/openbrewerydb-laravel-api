<?php

use App\Mcp\Servers\OpenBreweryDbServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', OpenBreweryDbServer::class)
    ->middleware('throttle:mcp');
