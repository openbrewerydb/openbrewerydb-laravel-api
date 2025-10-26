<?php

namespace App\Http\Requests\V1;

use App\Rules\BreweryType as BreweryTypeRule;
use App\Rules\Coordinates as CoordinatesRule;
use Illuminate\Foundation\Http\FormRequest;

class BreweryFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'required', 'integer', 'min:1', 'max:200'],
            'page' => ['sometimes', 'required', 'integer', 'min:1'],
            'sort' => ['sometimes', 'required', 'string'],

            // filters
            'by_city' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'by_country' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'by_dist' => ['sometimes', 'required', 'string', new CoordinatesRule],
            'by_dist_radius' => ['sometimes', 'required', 'numeric', 'min:0.1', 'max:10000'],
            'by_dist_unit' => ['sometimes', 'required', 'string', 'in:km,mi'],
            'by_ids' => ['sometimes', 'required', 'string', 'min:3', 'max:255'], // TODO: validate as array of uuid v4
            'by_name' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'by_postal' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'by_state' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'by_type' => ['sometimes', 'required', 'string', new BreweryTypeRule],
            'exclude_types' => ['sometimes', 'required', 'string', new BreweryTypeRule],
        ];
    }
}
