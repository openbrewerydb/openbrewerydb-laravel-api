<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BreweryResource;
use App\Models\Brewery;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

#[Group(name: 'Breweries', weight: 2)]
class SearchBreweries extends Controller
{
    /**
     * Search breweries.
     *
     * Search for a brewery by its attributes.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'per_page' => ['sometimes', 'required', 'integer', 'min:1', 'max:200'],
            'query' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        $query = urldecode(string: $request->string('query')->trim());

        $breweries = Brewery::search(query: $query)
            ->simplePaginate(perPage: $request->integer('per_page', 50));

        return response()->json(
            data: BreweryResource::collection($breweries),
            status: Response::HTTP_OK,
        );
    }
}
