<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Brewery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\QueryBuilder;

class ListBreweries extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // TODO: filter by distance

        $validator = Validator::make($request->all(), [
            'per_page' => 'integer|min:1|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

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
