<?php

namespace App\Models\Traits\V1;

use Illuminate\Database\Eloquent\Builder;

trait BreweryFilters
{
    /**
     * Scope a query to apply filters.
     */
    public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when(array_key_exists('by_city', $filters), function (Builder $query) use ($filters) {
                $pattern = $filters['by_city'];

                $query->whereLike('city', "%{$pattern}%");
            })
            ->when(array_key_exists('by_country', $filters), function (Builder $query) use ($filters) {
                $pattern = $filters['by_country'];

                $query->whereLike('country', "%{$pattern}%");
            })
            ->when(array_key_exists('by_dist', $filters), function (Builder $query) use ($filters) {
                [$latitude, $longitude] = array_map('trim', explode(',', $filters['by_dist']));
                $radius = $filters['by_dist_radius'] ?? null;
                $unit = $filters['by_dist_unit'] ?? 'mi';

                $query->orderByDistance($latitude, $longitude, $radius, $unit);
            })
            ->when(array_key_exists('by_ids', $filters), function (Builder $query) use ($filters) {
                $values = array_map('trim', explode(',', $filters['by_ids']));

                $query->whereIn('id', $values);
            })
            ->when(array_key_exists('by_name', $filters), function (Builder $query) use ($filters) {
                $pattern = $filters['by_name'];

                $query->whereLike('name', "%{$pattern}%");
            })
            ->when(array_key_exists('by_postal', $filters), function (Builder $query) use ($filters) {
                $pattern = $filters['by_postal'];

                $query->whereLike('postal_code', "%{$pattern}%");
            })
            ->when(array_key_exists('by_state', $filters), function (Builder $query) use ($filters) {
                $pattern = $filters['by_state'];

                $query->whereLike('state_province', "%{$pattern}%");
            })
            ->when(array_key_exists('by_type', $filters), function (Builder $query) use ($filters) {
                $types = array_map('trim', explode(',', $filters['by_type']));

                $query->whereIn('brewery_type', $types);
            })
            ->when(array_key_exists('exclude_types', $filters), function (Builder $query) use ($filters) {
                $types = array_map('trim', explode(',', $filters['exclude_types']));

                $query->whereNotIn('brewery_type', $types);
            });
    }

    /**
     * Scope a query to apply sorts.
     */
    public function scopeApplySorts(Builder $query, array $sorts): Builder
    {
        return $query
            ->when(array_key_exists('sort', $sorts), function (Builder $query) use ($sorts) {
                $values = explode(',', $sorts['sort']);

                $values = collect($values)
                    ->map(function ($value) {
                        return array_map('trim', explode(':', $value));
                    })
                    ->toArray();

                foreach ($values as $value) {
                    $field = $value[0] === 'type' ? 'brewery_type' : $value[0];

                    $query->orderBy($field, $value[1] ?? 'asc');
                }
            });
    }
}
