<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BreweryResource;
use App\Models\Brewery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SearchBreweries extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $query = urldecode(string: $request->string('query')->trim());

        $breweries = Brewery::search(query: $query)
            ->simplePaginate($request->input('per_page', 50));

        return response()->json(data: BreweryResource::collection($breweries));
    }
}
