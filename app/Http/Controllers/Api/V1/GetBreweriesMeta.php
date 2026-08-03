<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Breweries\GetBreweryMetadata;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BreweryFilterRequest;
use App\Http\Resources\V1\BreweryMetaResource;
use Illuminate\Http\Response;

class GetBreweriesMeta extends Controller
{
    /**
     * Get metadata about the brewery
     *
     * Takes the same filters as List Breweries.
     */
    public function __invoke(BreweryFilterRequest $request, GetBreweryMetadata $getBreweryMetadata)
    {
        $metadata = $getBreweryMetadata->handle($request->validated());
        $metadata['page'] = $request->integer('page', 1);
        $metadata['per_page'] = $request->integer('per_page', 50);

        return response()->json(
            data: new BreweryMetaResource($metadata),
            status: Response::HTTP_OK,
            headers: ['Cache-Control' => 'public, max-age=300, etag'],
        );
    }
}
