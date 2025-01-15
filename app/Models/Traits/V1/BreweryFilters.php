<?php

namespace App\Models\Traits\V1;

trait BreweryFilters
{
    /**
     * Order results by distance from given coordinates. Use "6371" for kilometers or "3959" for miles.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  float  $latitude
     * @param  float  $longitude
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByDistance($query, $latitude, $longitude)
    {
        $haversine = "(3959 * acos(cos(radians($latitude))
                        * cos(radians(latitude))
                        * cos(radians(longitude)
                        - radians($longitude))
                        + sin(radians($latitude))
                        * sin(radians(latitude))))";

        return $query->selectRaw("{$haversine} AS distance")
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('distance');
    }
}
