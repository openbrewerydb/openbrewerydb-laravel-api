<?php

namespace App\Actions\Breweries;

use App\Models\Brewery;
use Illuminate\Support\Facades\DB;

class GetBreweryMetadata
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{total: int, by_state: object, by_country: object, by_type: object}
     */
    public function handle(array $filters): array
    {
        $baseQuery = Brewery::query()->applyFilters($filters);

        $byState = (clone $baseQuery)
            ->reorder()
            ->select('state_province', DB::raw('count(*) as count'))
            ->whereNotNull('state_province')
            ->groupBy('state_province')
            ->pluck('count', 'state_province')
            ->map(fn (mixed $count): int => (int) $count)
            ->all();

        $byCountry = (clone $baseQuery)
            ->reorder()
            ->select('country', DB::raw('count(*) as count'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->pluck('count', 'country')
            ->map(fn (mixed $count): int => (int) $count)
            ->all();

        $byType = (clone $baseQuery)
            ->reorder()
            ->select('brewery_type', DB::raw('count(*) as count'))
            ->whereNotNull('brewery_type')
            ->groupBy('brewery_type')
            ->pluck('count', 'brewery_type')
            ->mapWithKeys(fn (mixed $count, string $type): array => [mb_strtolower($type) => (int) $count])
            ->all();

        return [
            'total' => (clone $baseQuery)->reorder()->count(),
            'by_state' => (object) $byState,
            'by_country' => (object) $byCountry,
            'by_type' => (object) $byType,
        ];
    }
}
