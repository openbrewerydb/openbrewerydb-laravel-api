<?php

namespace App\Models\Traits\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait BreweryFilters
{
    /**
     * Scope a query to apply filters.
     */
    public function scopeApplyFilters(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->has('by_city'), function (Builder $query) use ($request) {
                $pattern = urldecode($request->input('by_city'));

                $query->whereLike('city', "%{$pattern}%");
            })
            ->when($request->has('by_country'), function (Builder $query) use ($request) {
                $pattern = urldecode($request->input('by_country'));

                $query->whereLike('country', "%{$pattern}%");
            })
            // ->when($request->has('by_dist'), function (Builder $query) use ($request) {
            //     [$latitude, $longitude] = explode(',', $request->input('by_dist'));

            //     $query->orderByDistance($latitude, $longitude);
            // })
            ->when($request->has('by_ids'), function (Builder $query) use ($request) {
                $values = array_map('trim', explode(',', $request->input('by_ids')));

                $query->whereIn('id', $values);
            })
            ->when($request->has('by_name'), function (Builder $query) use ($request) {
                $pattern = urldecode($request->input('by_name'));

                $query->whereLike('name', "%{$pattern}%");
            })
            ->when($request->has('by_postal'), function (Builder $query) use ($request) {
                $pattern = urldecode($request->input('by_postal'));

                $query->whereLike('postal_code', "%{$pattern}%");
            })
            ->when($request->has('by_state'), function (Builder $query) use ($request) {
                $pattern = urldecode($request->input('by_state'));

                $query->whereLike('state_province', "%{$pattern}%");
            })
            ->when($request->has('by_type'), function (Builder $query) use ($request) {
                $types = array_map('trim', explode(',', $request->input('by_type')));

                $query->whereIn('brewery_type', $types);
            })
            ->when($request->has('exclude_types'), function (Builder $query) use ($request) {
                $types = array_map('trim', explode(',', $request->input('exclude_types')));

                $query->whereNotIn('brewery_type', $types);
            });
    }

    /**
     * Scope a query to apply sorts.
     */
    public function scopeApplySorts(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->has('by_dist'), function (Builder $query) use ($request) {
                [$latitude, $longitude] = array_map('trim', explode(',', $request->input('by_dist')));

                $query->orderByDistance($latitude, $longitude);
            })
            ->when($request->has('sort'), function (Builder $query) use ($request) {
                $values = explode(',', $request->input('sort'));

                $values = collect($values)
                    ->map(function ($value) {
                        return array_map('trim', explode(':', $value));
                    })
                    ->toArray();

                foreach ($values as $value) {
                    $query->orderBy($value[0], $value[1] ?? 'asc');
                }
            });
    }
}
