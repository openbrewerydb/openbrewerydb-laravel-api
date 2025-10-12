<?php

namespace App\Models;

use App\Enums\BreweryType;
use App\Models\Traits\V1\BreweryFilters as v1BreweryFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Brewery extends Model
{
    /** @use HasFactory<\Database\Factories\BreweryFactory> */
    use HasFactory, HasVersion4Uuids, Searchable, v1BreweryFilters;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'brewery_type' => BreweryType::class,
        ];
    }

    /**
     * Scope results by distance from given coordinates. Use "6371" for kilometers or "3959" for miles.
     */
    public function scopeOrderByDistance(Builder $query, float $latitude, float $longitude, ?float $radius = null, string $unit = 'mi'): Builder
    {  
        \Log::info("Ordering by distance from [{$latitude}, {$longitude}] within radius {$radius} {$unit}");

        $earthRadius = $unit === 'km' ? 6371 : 3959;

        $haversine = "({$earthRadius} * acos(cos(radians({$latitude}))
                        * cos(radians(latitude))
                        * cos(radians(longitude)
                        - radians({$longitude}))
                        + sin(radians({$latitude}))
                        * sin(radians(latitude))))";

        \Log::info("Haversine formula: {$haversine}");

        $query = $query->select('*')
            ->selectRaw("{$haversine} AS distance")
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        

        if ($radius !== null) {
            $query->whereRaw("{$haversine} <= {$radius}");
             \Log::info("SQL Query: ", [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
                'radius' => $radius,
                'full_condition' => "{$haversine} <= {$radius}"
            ]);
        }

        $query = $query->orderBy('distance');

        $log_results = (clone $query)->get();
        \Log::info("Distance Results: ", [
            'count' => (clone $query)->count(),
            'log_distances' => $log_results->map(fn($b) => [
                'name' => $b->name, 
                'latitude' => $b->latitude, 
                'longitude' => $b->longitude,
                'distance' => $b->distance
            ])    
        ]);

        return $query;
        
       
    }
        
}
