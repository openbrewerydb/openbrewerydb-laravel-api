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

#[Description('Full-text search Open Brewery DB. Use this for free-form brewery name searches; use list-breweries for structured filters.')]
#[Name('search-breweries')]
#[IsReadOnly]
#[IsDestructive(false)]
#[IsIdempotent]
#[IsOpenWorld(false)]
class SearchBreweriesTool extends BreweryTool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:3', 'max:255'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'between:1,50'],
        ], [
            'query.required' => 'Provide a brewery name or search phrase.',
            'query.min' => 'The search query must contain at least 3 characters.',
            'per_page.between' => 'Request between 1 and 50 search results per page.',
        ]);

        $page = $validated['page'] ?? 1;
        $perPage = $validated['per_page'] ?? 10;
        $breweries = Brewery::search(trim($validated['query']))
            ->simplePaginate($perPage, 'page', $page);

        return Response::structured([
            'breweries' => $breweries->getCollection()
                ->map(fn (Brewery $brewery): array => $this->serializeBrewery($brewery))
                ->values()
                ->all(),
            'pagination' => [
                'page' => $breweries->currentPage(),
                'per_page' => $breweries->perPage(),
                'has_more' => $breweries->hasMorePages(),
                'next_page' => $breweries->hasMorePages() ? $breweries->currentPage() + 1 : null,
            ],
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
            'query' => $schema->string()->min(3)->max(255)->description('Brewery name or phrase to search for.')->required(),
            'page' => $schema->integer()->min(1)->default(1)->description('Result page to return.'),
            'per_page' => $schema->integer()->min(1)->max(50)->default(10)->description('Number of search results to return per page.'),
        ];
    }

    /**
     * @return array<string, Type>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'breweries' => $schema->array()->items($this->brewerySchema($schema))->required(),
            'pagination' => $this->paginationSchema($schema, includesTotals: false)->required(),
        ];
    }
}
