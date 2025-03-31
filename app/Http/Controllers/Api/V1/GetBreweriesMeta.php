<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BreweryMetaResource;
use App\Models\Brewery;
use App\Rules\BreweryType as BreweryTypeRule;
use App\Rules\Coordinates as CoordinatesRule;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class GetBreweriesMeta extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            // filters
            'by_city' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'by_country' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'by_dist' => ['sometimes', 'required', 'string', new CoordinatesRule],
            'by_ids' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'by_name' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'by_postal' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'by_state' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'by_type' => ['sometimes', 'required', 'string', new BreweryTypeRule],
            'exclude_types' => ['sometimes', 'required', 'string', new BreweryTypeRule],
        ]);

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

        $data = new BreweryMetaResource([
            'total' => $total,
            'by_state' => $byState,
            'by_type' => $byType,
        ]);

        return response()->json(
            data: $data,
            status: Response::HTTP_OK,
            headers: ['Cache-Control' => 'public; max-age=300; etag'],
        );
    }
}
