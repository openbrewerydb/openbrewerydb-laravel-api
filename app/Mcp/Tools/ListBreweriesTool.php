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

#[Description('Browse the Open Brewery DB dataset using location, type, name, ID, and distance filters. Results are paginated and may be sorted by an allow-listed field.')]
#[Name('list-breweries')]
#[IsReadOnly]
#[IsDestructive(false)]
#[IsIdempotent]
#[IsOpenWorld(false)]
class ListBreweriesTool extends BreweryTool
{
    private const SORTABLE_FIELDS = [
        'id',
        'name',
        'brewery_type',
        'city',
        'state_province',
        'postal_code',
        'country',
    ];

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            ...$this->filterRules(),
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'between:1,50'],
            'sort_by' => ['sometimes', 'string', 'in:'.implode(',', self::SORTABLE_FIELDS)],
            'sort_order' => ['sometimes', 'string', 'in:asc,desc'],
        ], [
            'per_page.between' => 'Request between 1 and 50 breweries per page.',
            'sort_by.in' => 'Sort by one of: '.implode(', ', self::SORTABLE_FIELDS).'.',
            'sort_order.in' => 'The sort order must be asc or desc.',
        ]);

        $page = $validated['page'] ?? 1;
        $perPage = $validated['per_page'] ?? 10;
        $criteria = $this->criteria($validated);
        $criteria['sort'] = ($validated['sort_by'] ?? 'name').':'.($validated['sort_order'] ?? 'asc');

        $breweries = Brewery::query()
            ->applyFilters($criteria)
            ->applySorts($criteria)
            ->paginate($perPage, ['*'], 'page', $page);

        return Response::structured([
            'breweries' => $breweries->getCollection()
                ->map(fn (Brewery $brewery): array => $this->serializeBrewery($brewery))
                ->values()
                ->all(),
            'pagination' => [
                'page' => $breweries->currentPage(),
                'per_page' => $breweries->perPage(),
                'total' => $breweries->total(),
                'last_page' => $breweries->lastPage(),
                'has_more' => $breweries->hasMorePages(),
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
            ...$this->filterSchema($schema),
            'page' => $schema->integer()->min(1)->default(1)->description('Result page to return.'),
            'per_page' => $schema->integer()->min(1)->max(50)->default(10)->description('Number of breweries to return per page.'),
            'sort_by' => $schema->string()->enum(self::SORTABLE_FIELDS)->default('name')->description('Field used to sort breweries.'),
            'sort_order' => $schema->string()->enum(['asc', 'desc'])->default('asc')->description('Sort direction.'),
        ];
    }

    /**
     * @return array<string, Type>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'breweries' => $schema->array()->items($this->brewerySchema($schema))->required(),
            'pagination' => $this->paginationSchema($schema, includesTotals: true)->required(),
        ];
    }
}
