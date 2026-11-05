<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BreweryResource;
use App\Models\Brewery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class GetBrewery extends Controller
{
    /**
     * Get a single brewery.
     */
    public function __invoke(Brewery $brewery): JsonResponse
    {
        return response()->json(
            data: new BreweryResource($brewery),
            status: Response::HTTP_OK,
        );
    }
}
