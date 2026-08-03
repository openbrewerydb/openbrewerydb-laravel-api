<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class BrewerySort implements ValidationRule
{
    public const SORTABLE_FIELDS = [
        'id',
        'name',
        'brewery_type',
        'type',
        'city',
        'state_province',
        'postal_code',
        'country',
    ];

    private const SORT_DIRECTIONS = ['asc', 'desc'];

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        foreach (explode(',', $value) as $sort) {
            $parts = array_map('trim', explode(':', $sort));
            $field = $parts[0];
            $direction = strtolower($parts[1] ?? 'asc');

            if (count($parts) > 2 || ! in_array($field, self::SORTABLE_FIELDS, true)) {
                $fail('The :attribute contains an invalid sort field. Valid fields are: '.implode(', ', self::SORTABLE_FIELDS).'.');

                return;
            }

            if (! in_array($direction, self::SORT_DIRECTIONS, true)) {
                $fail('The :attribute direction must be asc or desc.');

                return;
            }
        }
    }
}
