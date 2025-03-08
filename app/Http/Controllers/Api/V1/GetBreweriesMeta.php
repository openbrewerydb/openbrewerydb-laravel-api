<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BreweryMetaResource;
use App\Models\Brewery;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GetBreweriesMeta extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $data = Cache::remember('brewery_meta', 300, function () {
            $total = Brewery::count();

            $byState = Brewery::query()
                ->select('state_province', DB::raw('count(*) as count'))
                ->whereNotNull('state_province')
                ->groupBy('state_province')
                ->pluck('count', 'state_province')
                ->toArray();

            $byType = Brewery::query()
                ->select('brewery_type', DB::raw('count(*) as count'))
                ->whereNotNull('brewery_type')
                ->groupBy('brewery_type')
                ->pluck('count', 'brewery_type')
                ->mapWithKeys(function ($count, $type) {
                    return [strtolower($type) => $count];
                })
                ->toArray();

            return new BreweryMetaResource([
                'total' => $total,
                'by_state' => $byState,
                'by_type' => $byType,
            ]);
        });

        return response()->json(
            data: $data,
            status: Response::HTTP_OK,
            headers: ['Cache-Control' => 'public; max-age=300; etag'],
        );
    }
}
