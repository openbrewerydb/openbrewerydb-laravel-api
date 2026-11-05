<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BreweryResource;
use App\Models\Brewery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RandomBrewery extends Controller
{
    /**
     * Get a random brewery.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'size' => ['sometimes', 'required', 'integer', 'min:1', 'max:50'],
        ]);

        $breweries = Brewery::inRandomOrder()
            ->limit($request->integer('size', 1))
            ->get();

        return response()->json(
            data: BreweryResource::collection($breweries),
            status: Response::HTTP_OK,
            // No caching for random events
        );
    }
}
