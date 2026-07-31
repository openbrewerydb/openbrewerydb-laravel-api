<?php

namespace App\Mcp\Tools;

use App\Models\Brewery;
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

#[Description('Retrieve one brewery from Open Brewery DB by its UUID.')]
#[Name('get-brewery')]
#[IsReadOnly]
#[IsDestructive(false)]
#[IsIdempotent]
#[IsOpenWorld(false)]
class GetBreweryTool extends BreweryTool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'id' => ['required', 'uuid'],
        ], [
            'id.required' => 'Provide the UUID of the brewery to retrieve.',
            'id.uuid' => 'The brewery ID must be a valid UUID.',
        ]);

        $brewery = Brewery::find($validated['id']);

        if ($brewery === null) {
            return Response::error("No brewery was found with ID [{$validated['id']}].");
        }

        return Response::structured([
            'brewery' => $this->serializeBrewery($brewery),
        ]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->string()->format('uuid')->description('The brewery UUID returned by Open Brewery DB.')->required(),
        ];
    }

    /**
     * @return array<string, Type>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'brewery' => $this->brewerySchema($schema)->required(),
        ];
    }
}
