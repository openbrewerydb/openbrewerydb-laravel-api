<?php

namespace App\Mcp\Tools;

use App\Actions\Breweries\GetBreweryMetadata;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Count breweries in Open Brewery DB and group the results by state or province, country, and brewery type. The same filters as list-breweries may be applied.')]
#[Name('get-brewery-metadata')]
#[IsReadOnly]
#[IsDestructive(false)]
#[IsIdempotent]
#[IsOpenWorld(false)]
class GetBreweryMetadataTool extends BreweryTool
{
    public function __construct(private GetBreweryMetadata $getBreweryMetadata) {}

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): ResponseFactory
    {
        $rules = $this->filterRules();
        $rules['radius'] = ['required_with:latitude,longitude', 'numeric', 'between:0.1,10000'];

        $validated = $request->validate($rules, [
            'radius.required_with' => 'Provide a radius when filtering metadata by coordinates.',
        ]);

        return Response::structured(
            $this->getBreweryMetadata->handle($this->criteria($validated)),
        );
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        $filters = $this->filterSchema($schema);
        $filters['radius'] = $schema->number()->min(0.1)->max(10000)->description('Required with coordinates. Only breweries within this distance are included in the metadata.');

        return [
            ...$filters,
        ];
    }

    /**
     * @return array<string, Type>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->integer()->min(0)->required(),
            'by_state' => $schema->object()->description('Brewery counts keyed by state or province.')->required(),
            'by_country' => $schema->object()->description('Brewery counts keyed by country.')->required(),
            'by_type' => $schema->object()->description('Brewery counts keyed by brewery type.')->required(),
        ];
    }
}
