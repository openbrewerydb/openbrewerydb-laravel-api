<?php

namespace App\Models\Traits\V1;

use App\Enums\BreweryType;

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

    /**
     * Filter breweries by type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $types  Comma-separated list of brewery types
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, string $types)
    {
        $typeArray = array_map('trim', explode(',', strtolower($types)));

        // Validate each type against the enum
        $validTypes = [];
        foreach ($typeArray as $type) {
            try {
                // This will throw an exception if the type is invalid
                $enumType = BreweryType::from($type);
                $validTypes[] = $enumType->value;
            } catch (\ValueError $e) {
                // Skip invalid types
                continue;
            }
        }

        if (empty($validTypes)) {
            return $query->whereRaw('1 = 0'); // Return no results for invalid types
        }

        return $query->whereIn('brewery_type', $validTypes);
    }
}
