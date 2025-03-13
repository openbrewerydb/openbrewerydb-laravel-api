<?php

namespace App\Rules;

use App\Enums\BreweryType as BreweryTypeEnum;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BreweryType implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $types = array_map('trim', explode(',', $value));

        foreach ($types as $type) {
            if (! BreweryTypeEnum::tryFrom($type)) {
                $fail("The {$attribute} contains invalid brewery type: {$type}");
            }
        }
    }
}
