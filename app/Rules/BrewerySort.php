<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BrewerySort implements ValidationRule
{
    /**
     * Valid field names for sorting.
     */
    protected array $validFields = [
        'id', 'name', 'brewery_type', 'city', 'state_province',
        'country', 'postal_code', 'phone', 'website_url',
        'created_at', 'updated_at',
    ];

    /**
     * Valid sort directions.
     */
    protected array $validDirections = ['asc', 'desc'];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Split by comma to handle multiple sort fields
        $sortParams = explode(',', $value);

        foreach ($sortParams as $param) {
            $param = trim($param);

            // If the parameter contains a colon, it includes a sort direction
            if (str_contains($param, ':')) {
                [$field, $direction] = explode(':', $param, 2);
                $field = trim($field);
                $direction = trim($direction);

                // Check if the field is valid
                if (! in_array($field, $this->validFields)) {
                    $fail("The sort field '$field' is not valid. Valid fields are: ".implode(', ', $this->validFields));

                    return;
                }

                // Check if the direction is valid
                if (! in_array(strtolower($direction), $this->validDirections)) {
                    $fail("The sort direction '$direction' is not valid. Valid directions are: ".implode(', ', $this->validDirections));

                    return;
                }
            }

            if (! str_contains($param, ':') && ! in_array($param, $this->validFields)) {
                $fail("The sort field '$param' is not valid. Valid fields are: ".implode(', ', $this->validFields));

                return;
            }
        }
    }
}
