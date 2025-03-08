<?php

namespace App\Models;

use App\Enums\BreweryType;
use App\Models\Traits\V1\BreweryFilters as v1BreweryFilters;
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
}
