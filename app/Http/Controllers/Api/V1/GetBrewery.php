<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Brewery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetBrewery extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $brewery = Brewery::findOrFail($id);

        return response()->json($brewery);
    }
}
