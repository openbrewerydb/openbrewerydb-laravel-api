<?php

namespace App\Console\Commands\HealthCheck\Checkers;

use App\Console\Commands\HealthCheck\HealthCheckerInterface;
use App\Models\Brewery;

class SearchHealthChecker implements HealthCheckerInterface
{
    /**
     * Run the health check.
     *
     * @param  bool  $detailed  Whether to show detailed information
     * @return array Array with success status and any issues found
     */
    public function check(bool $detailed = false): array
    {
        $issues = [];
        $details = [];

        // Check 1: Meilisearch connection
        try {
            // Access Meilisearch client directly
            $client = app(\Meilisearch\Client::class);
            $health = $client->health();

            if ($health['status'] !== 'available') {
                $issues[] = "Meilisearch is not available. Status: {$health['status']}";
            }
        } catch (\Exception $e) {
            $issues[] = 'Error connecting to Meilisearch: '.$e->getMessage();
        }

        // Check 2: Search index existence and health
        try {
            // Access Meilisearch client directly
            $client = app(\Meilisearch\Client::class);
            $indexName = (new Brewery)->searchableAs();

            try {
                $client->index($indexName)->stats();
            } catch (\Exception $e) {
                // If we get an exception, the index doesn't exist
                $issues[] = "Search index '{$indexName}' does not exist";

                return [
                    'success' => false,
                    'issues' => $issues,
                    'details' => $details,
                ];
            }

            // If we get here, the index exists
            $stats = $client->index($indexName)->stats();
            $dbCount = Brewery::count();

            if ($stats['numberOfDocuments'] !== $dbCount) {
                $issues[] = "Search index document count ({$stats['numberOfDocuments']}) doesn't match database count ({$dbCount})";
            }

            if ($detailed) {
                $details[] = "Index '{$indexName}' has {$stats['numberOfDocuments']} documents";
            }
        } catch (\Exception $e) {
            $issues[] = 'Error checking search indexes: '.$e->getMessage();
        }

        // Check 3: Search index settings
        try {
            // Access Meilisearch client directly
            $client = app(\Meilisearch\Client::class);
            $indexName = (new Brewery)->searchableAs();

            $settings = $client->index($indexName)->getSettings();

            $expectedFilterable = ['brewery_type', 'city', 'state_province', 'country'];
            $expectedSortable = ['name'];

            $missingFilterable = array_diff($expectedFilterable, $settings['filterableAttributes'] ?? []);
            $missingSortable = array_diff($expectedSortable, $settings['sortableAttributes'] ?? []);

            if (! empty($missingFilterable)) {
                $issues[] = 'Missing filterable attributes in search index: '.implode(', ', $missingFilterable);
            }

            if (! empty($missingSortable)) {
                $issues[] = 'Missing sortable attributes in search index: '.implode(', ', $missingSortable);
            }
        } catch (\Exception $e) {
            $issues[] = 'Error checking search index settings: '.$e->getMessage();
        }

        return [
            'success' => empty($issues),
            'issues' => $issues,
            'details' => $details,
        ];
    }

    /**
     * Get the name of the health check.
     */
    public function getName(): string
    {
        return 'Search';
    }
}
