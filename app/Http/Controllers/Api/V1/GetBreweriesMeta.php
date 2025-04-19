<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BreweryFilterRequest;
use App\Http\Resources\V1\BreweryMetaResource;
use App\Models\Brewery;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class GetBreweriesMeta extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(BreweryFilterRequest $request)
    {

        // Create a base query that applies filters
        $baseQuery = Brewery::query()->applyFilters($request);

        // Get total count with filters applied
        $total = $baseQuery->count();

        // Get by_state with filters applied
        $byState = (clone $baseQuery)
            ->select('state_province', DB::raw('count(*) as count'))
            ->whereNotNull('state_province')
            ->groupBy('state_province')
            ->pluck('count', 'state_province')
            ->toArray();

        // Get by_type with filters applied
        $byType = (clone $baseQuery)
            ->select('brewery_type', DB::raw('count(*) as count'))
            ->whereNotNull('brewery_type')
            ->groupBy('brewery_type')
            ->pluck('count', 'brewery_type')
            ->mapWithKeys(function ($count, $type) {
                return [strtolower($type) => $count];
            })
            ->toArray();

        $page = $request->integer('page', 1);
        $perPage = $request->integer('per_page', 50);

        $data = new BreweryMetaResource([
            'total' => $total,
            'by_state' => $byState,
            'by_type' => $byType,
            'page' => $page,
            'per_page' => $perPage,
        ]);

        return response()->json(
            data: $data,
            status: Response::HTTP_OK,
            headers: ['Cache-Control' => 'public; max-age=300; etag'],
        );
    }
}
