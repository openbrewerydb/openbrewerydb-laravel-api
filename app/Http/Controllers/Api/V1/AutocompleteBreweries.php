<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AutocompleteBreweries extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $query = $request->query('query');

        Log::info('Redirecting autocomplete to search', ['query' => $query]);

        return redirect()->route(
            route: 'v1.breweries.search',
            parameters: [
                'query' => $query,
            ],
            status: 301,
        );
    }
}
