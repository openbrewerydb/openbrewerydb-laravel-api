<?php

namespace App\Http\Requests\V1;

use App\Enums\BreweryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateBreweryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $merges = [];

        if ($this->filled('street') && ! $this->filled('address_1')) {
            $merges['address_1'] = $this->input('street');
        }

        if ($this->filled('state') && ! $this->filled('state_province')) {
            $merges['state_province'] = $this->input('state');
        }

        if ($this->has('brewery_type')) {
            $rawType = is_string($this->input('brewery_type')) ? strtolower(trim($this->input('brewery_type'))) : '';
            $typeEnum = BreweryType::tryFrom($rawType);
            $merges['brewery_type'] = $typeEnum ? $typeEnum->value : BreweryType::Unknown->value;
        } else {
            $merges['brewery_type'] = BreweryType::Unknown->value;
        }

        if (! empty($merges)) {
            $this->merge($merges);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'brewery_type' => ['sometimes', 'required', 'string', new Enum(BreweryType::class)],
            'address_1' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address_2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address_3' => ['sometimes', 'nullable', 'string', 'max:255'],
            'street' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state_province' => ['required', 'string', 'max:255'],
            'state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:50'],
            'website_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * Get the brewery attributes to be persisted.
     *
     * @return array<string, mixed>
     */
    public function breweryAttributes(): array
    {
        return $this->safe()->only([
            'name',
            'brewery_type',
            'address_1',
            'address_2',
            'address_3',
            'city',
            'state_province',
            'country',
            'postal_code',
            'website_url',
            'phone',
            'latitude',
            'longitude',
        ]);
    }
}
