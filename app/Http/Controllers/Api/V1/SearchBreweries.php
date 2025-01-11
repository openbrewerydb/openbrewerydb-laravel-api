<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BreweryCollection;
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

        $breweries = Brewery::search(query: urldecode($request->input('query')))
            ->simplePaginate($request->input('per_page', 50));

        return response()->json(new BreweryCollection($breweries));
    }
}
