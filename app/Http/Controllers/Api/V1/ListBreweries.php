<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BreweryFilterRequest;
use App\Http\Resources\V1\BreweryResource;
use App\Models\Brewery;
use Illuminate\Http\Response;

class ListBreweries extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(BreweryFilterRequest $request)
    {

        $breweries = Brewery::query()
            ->applyFilters($request)
            ->applySorts($request)
            ->paginate(perPage: $request->integer('per_page', 50));

        return response()->json(
            BreweryResource::collection($breweries),
            Response::HTTP_OK,
        );
    }
}
