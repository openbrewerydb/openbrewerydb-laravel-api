<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Brewery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Autocomplete extends Controller
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
            ->query(function ($query) {
                $query->select(['id', 'name']);
            })
            ->take(15)
            ->get();

        return response()->json($breweries);
    }
}
