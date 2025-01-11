<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Brewery;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class ListBreweries extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // TODO: filter by distance

        $fields = ['id', 'name', 'city', 'province', 'country', 'postal_code', 'type'];

        $breweries = QueryBuilder::for(Brewery::class)
            ->allowedFilters($fields)
            ->defaultSort('name')
            ->allowedSorts($fields)
            ->simplePaginate(
                $request->input('per_page', 50)
            );

        return response()->json($breweries);
    }
}
