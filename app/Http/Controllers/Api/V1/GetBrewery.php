<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BreweryResource;
use App\Models\Brewery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class GetBrewery extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $brewery = Cache::remember('brewery_'.$id, 300, function () use ($id) {
            return new BreweryResource(Brewery::findOrFail($id));
        });

        return response()->json(
            data: $brewery,
            status: Response::HTTP_OK,
            headers: ['Cache-Control' => 'public, max-age=300, etag'],
        );
    }
}
