<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Coordinates implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Split the value by comma
        $parts = explode(',', $value);

        // Check if we have exactly two parts
        if (count($parts) !== 2) {
            $fail('The :attribute must be a valid coordinate pair (latitude,longitude).');

            return;
        }

        // Trim whitespace
        $latitude = trim($parts[0]);
        $longitude = trim($parts[1]);

        // Validate latitude: must be between -90 and 90
        if (! is_numeric($latitude) || $latitude < -90 || $latitude > 90) {
            $fail('The latitude in :attribute must be a number between -90 and 90.');

            return;
        }

        // Validate longitude: must be between -180 and 180
        if (! is_numeric($longitude) || $longitude < -180 || $longitude > 180) {
            $fail('The longitude in :attribute must be a number between -180 and 180.');

            return;
        }
    }
}
