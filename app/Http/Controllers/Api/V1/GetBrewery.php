<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BreweryResource;
use App\Models\Brewery;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

#[Group(name: 'Breweries', weight: 1)]
class GetBrewery extends Controller
{
    /**
     * Get brewery
     *
     * Get a brewery by ID.
     */
    #[PathParameter('id', description: 'UUID of the brewery.', type: 'string', example: '9fb357eb-965e-4920-81d0-256910248fc0', required: true)]
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $brewery = Cache::remember('brewery_'.$id, 300, function () use ($id) {
            return new BreweryResource(Brewery::findOrFail($id));
        });

        return response()->json(
            data: $brewery,
            status: Response::HTTP_OK,
            headers: ['Cache-Control' => 'public; max-age=300; etag'],
        );
    }
}
