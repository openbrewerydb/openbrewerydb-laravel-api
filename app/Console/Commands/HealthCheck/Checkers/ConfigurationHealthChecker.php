<?php

namespace App\Console\Commands\HealthCheck\Checkers;

use App\Console\Commands\HealthCheck\HealthCheckerInterface;

class ConfigurationHealthChecker implements HealthCheckerInterface
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

        // Check essential configuration settings
        $requiredConfigs = [
            'app.name' => 'Application name is not set',
            'app.url' => 'Application URL is not set',
            'database.default' => 'Default database connection is not set',
            'scout.driver' => 'Scout driver is not set',
        ];

        foreach ($requiredConfigs as $config => $message) {
            if (empty(config($config))) {
                $issues[] = $message;
            }
        }

        // Check Meilisearch configuration (based on memory)
        if (config('scout.driver') === 'meilisearch') {
            $meilisearchHost = config('scout.meilisearch.host');
            if (empty($meilisearchHost)) {
                $issues[] = 'Meilisearch host is not configured';
            } else {
                if ($detailed) {
                    $details[] = 'Meilisearch Host: '.$this->maskUrl($meilisearchHost);
                }
            }

            // Check for queue configuration for Scout
            $queueEnabled = config('scout.queue');
            if ($detailed) {
                $details[] = 'Scout Queue: '.($queueEnabled ? 'Enabled' : 'Disabled');
            }
        }

        // Check database configuration
        $dbConnection = config('database.default');
        if ($dbConnection) {
            $dbConfig = config("database.connections.{$dbConnection}");

            if ($detailed) {
                $details[] = 'Database Driver: '.($dbConfig['driver'] ?? 'Not set');

                // Add database connection details without exposing credentials
                if (isset($dbConfig['driver'])) {
                    switch ($dbConfig['driver']) {
                        case 'sqlite':
                            $details[] = 'SQLite Database: '.($dbConfig['database'] ?? 'Not set');
                            break;
                        case 'mysql':
                        case 'pgsql':
                            $details[] = 'Database Host: '.($dbConfig['host'] ?? 'Not set');
                            $details[] = 'Database Name: '.($dbConfig['database'] ?? 'Not set');
                            $details[] = 'Database Port: '.($dbConfig['port'] ?? 'Not set');
                            break;
                    }
                }
            }
        }

        // Check for proper filter and sort configuration for Meilisearch
        $expectedFilterable = ['brewery_type', 'city', 'state_province', 'country'];
        $expectedSortable = ['name'];

        // Instead of checking config values, check the actual index settings
        try {
            if (config('scout.driver') === 'meilisearch') {
                $client = app(\Meilisearch\Client::class);
                $indexName = (new \App\Models\Brewery)->searchableAs();

                try {
                    $settings = $client->index($indexName)->getSettings();

                    $filterableAttributes = $settings['filterableAttributes'] ?? [];
                    $sortableAttributes = $settings['sortableAttributes'] ?? [];

                    $missingFilterable = array_diff($expectedFilterable, $filterableAttributes);
                    $missingSortable = array_diff($expectedSortable, $sortableAttributes);

                    if (! empty($missingFilterable)) {
                        $issues[] = 'Missing filterable attributes in Meilisearch index: '.implode(', ', $missingFilterable);
                    }

                    if (! empty($missingSortable)) {
                        $issues[] = 'Missing sortable attributes in Meilisearch index: '.implode(', ', $missingSortable);
                    }

                    if ($detailed) {
                        $details[] = 'Filterable Attributes: '.(empty($filterableAttributes) ? 'None' : implode(', ', $filterableAttributes));
                        $details[] = 'Sortable Attributes: '.(empty($sortableAttributes) ? 'None' : implode(', ', $sortableAttributes));
                    }
                } catch (\Exception $e) {
                    // If we can't get the settings, don't report missing attributes
                    if ($detailed) {
                        $details[] = 'Could not retrieve Meilisearch index settings: '.$e->getMessage();
                    }
                }
            }
        } catch (\Exception $e) {
            // If there's an error connecting to Meilisearch, don't report missing attributes
            if ($detailed) {
                $details[] = 'Error checking Meilisearch configuration: '.$e->getMessage();
            }
        }

        // Check application environment
        $appEnv = config('app.env');
        $appDebug = config('app.debug');

        if ($appEnv === 'production' && $appDebug) {
            $issues[] = 'Debug mode is enabled in production environment';
        }

        if ($detailed) {
            $details[] = "Application Environment: {$appEnv}";
            $details[] = 'Debug Mode: '.($appDebug ? 'Enabled' : 'Disabled');
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
        return 'Configuration';
    }

    /**
     * Mask a URL to hide sensitive information.
     */
    protected function maskUrl(string $url): string
    {
        $parsedUrl = parse_url($url);

        if (isset($parsedUrl['user']) || isset($parsedUrl['pass'])) {
            // Mask username and password
            $auth = isset($parsedUrl['user']) ? '****' : '';
            $auth .= isset($parsedUrl['pass']) ? ':****' : '';

            $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'].'://' : '';
            $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
            $port = isset($parsedUrl['port']) ? ':'.$parsedUrl['port'] : '';
            $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
            $query = isset($parsedUrl['query']) ? '?'.$parsedUrl['query'] : '';
            $fragment = isset($parsedUrl['fragment']) ? '#'.$parsedUrl['fragment'] : '';

            return $scheme.$auth.'@'.$host.$port.$path.$query.$fragment;
        }

        return $url;
    }
}
