<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BreweryResource;
use App\Models\Brewery;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class RandomBrewery extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, int $size = 1)
    {
        $validator = Validator::make($request->all(), [
            'size' => 'integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $breweries = Brewery::inRandomOrder()
            ->limit($request->input('size', 1))
            ->get();

        return response()->json(
            data: BreweryResource::collection($breweries),
            status: Response::HTTP_OK,
            // No caching for random events
        );
    }
}
