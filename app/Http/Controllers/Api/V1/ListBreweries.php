<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BreweryResource;
use App\Models\Brewery;
use App\Rules\BreweryType as BreweryTypeRule;
use App\Rules\Coordinates as CoordinatesRule;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

#[Group(name: 'Breweries', weight: 0)]
class ListBreweries extends Controller
{
    /**
     * List breweries
     *
     * List all breweries.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'per_page' => ['sometimes', 'required', 'integer', 'min:1', 'max:200'],
            'page' => ['integer', 'min:1'],
            'sort' => ['string'],

            // filters
            'by_city' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'by_country' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'by_dist' => ['sometimes', 'required', 'string', new CoordinatesRule],
            'by_ids' => ['sometimes', 'required', 'string', 'min:3', 'max:255'], // ! this aint right, need to validate as array and that they're uuid v4
            'by_name' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'by_postal' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'by_state' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'by_type' => ['sometimes', 'required', 'string', new BreweryTypeRule],
            'exclude_types' => ['sometimes', 'required', 'string', new BreweryTypeRule],
        ]);

        $breweries = Brewery::query()
            ->applyFilters($request)
            ->applySorts($request)
            ->paginate($request->integer('per_page', 50));

        return response()->json(
            BreweryResource::collection($breweries),
            Response::HTTP_OK,
            [
                'Cache-Control' => 'max-age=300, public',
            ]
        );
    }
}
