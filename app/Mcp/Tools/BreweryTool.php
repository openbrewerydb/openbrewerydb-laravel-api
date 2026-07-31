<?php

namespace App\Mcp\Tools;

use App\Enums\BreweryType;
use App\Http\Resources\V1\BreweryResource;
use App\Models\Brewery;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\ObjectType;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Server\Tool;

abstract class BreweryTool extends Tool
{
    /**
     * @return array<string, Type>
     */
    protected function filterSchema(JsonSchema $schema): array
    {
        return [
            'city' => $schema->string()->min(3)->max(255)->description('Return breweries whose city contains this value.'),
            'country' => $schema->string()->min(3)->max(255)->description('Return breweries whose country contains this value.'),
            'name' => $schema->string()->min(3)->max(255)->description('Return breweries whose name contains this value.'),
            'postal_code' => $schema->string()->min(3)->max(255)->description('Return breweries whose postal code contains this value.'),
            'state' => $schema->string()->min(3)->max(255)->description('Return breweries whose state or province contains this value.'),
            'types' => $schema->array()->items($schema->string()->enum(BreweryType::class))->min(1)->max(count(BreweryType::cases()))->unique()->description('Only return these brewery types.'),
            'exclude_types' => $schema->array()->items($schema->string()->enum(BreweryType::class))->min(1)->max(count(BreweryType::cases()))->unique()->description('Exclude these brewery types.'),
            'ids' => $schema->array()->items($schema->string()->format('uuid'))->min(1)->max(50)->unique()->description('Only return breweries with these UUIDs.'),
            'latitude' => $schema->number()->min(-90)->max(90)->description('Latitude used to order or filter breweries by distance. Must be provided with longitude.'),
            'longitude' => $schema->number()->min(-180)->max(180)->description('Longitude used to order or filter breweries by distance. Must be provided with latitude.'),
            'radius' => $schema->number()->min(0.1)->max(10000)->description('Maximum distance from the required coordinates. Omit to only order by distance.'),
            'distance_unit' => $schema->string()->enum(['mi', 'km'])->description('Unit used for the distance radius. Defaults to miles when omitted.'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function filterRules(): array
    {
        return [
            'city' => ['sometimes', 'string', 'min:3', 'max:255'],
            'country' => ['sometimes', 'string', 'min:3', 'max:255'],
            'name' => ['sometimes', 'string', 'min:3', 'max:255'],
            'postal_code' => ['sometimes', 'string', 'min:3', 'max:255'],
            'state' => ['sometimes', 'string', 'min:3', 'max:255'],
            'types' => ['sometimes', 'array', 'min:1', 'max:'.count(BreweryType::cases())],
            'types.*' => ['string', Rule::enum(BreweryType::class)],
            'exclude_types' => ['sometimes', 'array', 'min:1', 'max:'.count(BreweryType::cases())],
            'exclude_types.*' => ['string', Rule::enum(BreweryType::class)],
            'ids' => ['sometimes', 'array', 'min:1', 'max:50'],
            'ids.*' => ['uuid'],
            'latitude' => ['required_with:longitude,radius', 'numeric', 'between:-90,90'],
            'longitude' => ['required_with:latitude,radius', 'numeric', 'between:-180,180'],
            'radius' => ['sometimes', 'numeric', 'between:0.1,10000'],
            'distance_unit' => ['sometimes', 'string', 'in:mi,km'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function criteria(array $validated): array
    {
        $criteria = [];
        $filterMap = [
            'city' => 'by_city',
            'country' => 'by_country',
            'name' => 'by_name',
            'postal_code' => 'by_postal',
            'state' => 'by_state',
        ];

        foreach ($filterMap as $input => $filter) {
            if (array_key_exists($input, $validated)) {
                $criteria[$filter] = $validated[$input];
            }
        }

        if (array_key_exists('types', $validated)) {
            $criteria['by_type'] = implode(',', $validated['types']);
        }

        if (array_key_exists('exclude_types', $validated)) {
            $criteria['exclude_types'] = implode(',', $validated['exclude_types']);
        }

        if (array_key_exists('ids', $validated)) {
            $criteria['by_ids'] = implode(',', $validated['ids']);
        }

        if (array_key_exists('latitude', $validated) && array_key_exists('longitude', $validated)) {
            $criteria['by_dist'] = $validated['latitude'].','.$validated['longitude'];
            $criteria['by_dist_unit'] = $validated['distance_unit'] ?? 'mi';

            if (array_key_exists('radius', $validated)) {
                $criteria['by_dist_radius'] = $validated['radius'];
            }
        }

        return $criteria;
    }

    protected function brewerySchema(JsonSchema $schema): ObjectType
    {
        return $schema->object([
            'id' => $schema->string()->format('uuid')->required(),
            'name' => $schema->string()->required(),
            'brewery_type' => $schema->string()->enum(BreweryType::class)->nullable()->required(),
            'address_1' => $schema->string()->nullable()->required(),
            'address_2' => $schema->string()->nullable()->required(),
            'address_3' => $schema->string()->nullable()->required(),
            'city' => $schema->string()->nullable()->required(),
            'state_province' => $schema->string()->nullable()->required(),
            'postal_code' => $schema->string()->nullable()->required(),
            'country' => $schema->string()->nullable()->required(),
            'longitude' => $schema->union(['string', 'number'])->nullable()->required(),
            'latitude' => $schema->union(['string', 'number'])->nullable()->required(),
            'phone' => $schema->string()->nullable()->required(),
            'website_url' => $schema->string()->nullable()->required(),
            'state' => $schema->string()->nullable()->required(),
            'street' => $schema->string()->nullable()->required(),
        ])->withoutAdditionalProperties();
    }

    protected function paginationSchema(JsonSchema $schema, bool $includesTotals): ObjectType
    {
        $properties = [
            'page' => $schema->integer()->min(1)->required(),
            'per_page' => $schema->integer()->min(1)->max(50)->required(),
            'has_more' => $schema->boolean()->required(),
        ];

        if ($includesTotals) {
            $properties['total'] = $schema->integer()->min(0)->required();
            $properties['last_page'] = $schema->integer()->min(1)->required();
        } else {
            $properties['next_page'] = $schema->integer()->min(2)->nullable()->required();
        }

        return $schema->object($properties)->withoutAdditionalProperties();
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeBrewery(Brewery $brewery): array
    {
        return BreweryResource::make($brewery)->resolve();
    }
}
