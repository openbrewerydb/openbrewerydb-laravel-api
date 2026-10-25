<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CreateBreweryRequest;
use App\Http\Resources\V1\BreweryResource;
use App\Models\Brewery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CreateBrewery extends Controller
{
    /**
     * Create a brewery.
     *
     * Upload an individual brewery, cidery, or craft beverage location.
     */
    public function __invoke(CreateBreweryRequest $request): JsonResponse
    {
        $brewery = Brewery::create($request->breweryAttributes());

        return response()
            ->json(
                data: new BreweryResource($brewery),
                status: Response::HTTP_CREATED,
                headers: [
                    'Location' => route('v1.breweries.show', ['id' => $brewery->id]),
                ],
            );
    }
}
