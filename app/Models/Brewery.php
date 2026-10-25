<?php

namespace App\Models;

use App\Enums\BreweryType;
use App\Models\Traits\V1\BreweryFilters as v1BreweryFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

/**
 * @property string $id
 * @property string $name
 * @property \App\Enums\BreweryType|null $brewery_type
 * @property string|null $address_1
 * @property string|null $address_2
 * @property string|null $address_3
 * @property string $city
 * @property string $state_province
 * @property string|null $postal_code
 * @property string $country
 * @property string|null $longitude
 * @property string|null $latitude
 * @property string|null $phone
 * @property string|null $website_url
 */
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
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'brewery_type',
        'address_1',
        'address_2',
        'address_3',
        'city',
        'state_province',
        'postal_code',
        'country',
        'longitude',
        'latitude',
        'phone',
        'website_url',
    ];

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
        $earthRadius = $unit === 'km' ? 6371 : 3959;

        $haversine = "({$earthRadius} * acos(cos(radians({$latitude}))
                        * cos(radians(latitude))
                        * cos(radians(longitude)
                        - radians({$longitude}))
                        + sin(radians({$latitude}))
                        * sin(radians(latitude))))";

        $query = $query->select('*')
            ->selectRaw("{$haversine} AS distance")
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($radius !== null) {
            $query->whereRaw("{$haversine} <= {$radius}");
        }

        $query = $query->orderBy('distance');

        return $query;
    }
}
