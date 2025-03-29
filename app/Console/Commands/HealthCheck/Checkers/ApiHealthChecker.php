<?php

namespace App\Console\Commands\HealthCheck\Checkers;

use App\Console\Commands\HealthCheck\HealthCheckerInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

class ApiHealthChecker implements HealthCheckerInterface
{
    /**
     * Maximum acceptable response time in milliseconds.
     *
     * @var int
     */
    protected $maxResponseTime = 2000;

    /**
     * Run the health check.
     *
     * @param bool $detailed Whether to show detailed information
     * @return array Array with success status and any issues found
     */
    public function check(bool $detailed = false): array
    {
        $issues = [];
        $details = [];

        // Get the application URL
        $appUrl = config('app.url');
        if (empty($appUrl)) {
            $issues[] = "Application URL is not configured";
            return [
                'success' => false,
                'issues' => $issues,
                'details' => $details,
            ];
        }

        // Check main API endpoints
        $endpoints = [
            '/v1/breweries' => 'GET',
            '/v1/breweries/random' => 'GET',
            '/v1/breweries/search?query=dog' => 'GET',
        ];

        foreach ($endpoints as $endpoint => $method) {
            try {
                $url = rtrim($appUrl, '/') . $endpoint;
                $startTime = microtime(true);

                $response = Http::timeout(5)->withHeaders([
                    'Accept' => 'application/json',
                ])->$method($url);

                $endTime = microtime(true);
                $responseTime = round(($endTime - $startTime) * 1000); // in milliseconds

                if (!$response->successful()) {
                    $issues[] = "Endpoint {$endpoint} returned status code {$response->status()}";
                }

                if ($responseTime > $this->maxResponseTime) {
                    $issues[] = "Endpoint {$endpoint} response time ({$responseTime}ms) exceeds maximum ({$this->maxResponseTime}ms)";
                }

                if ($detailed) {
                    $details[] = "Endpoint {$endpoint} - Status: {$response->status()}, Response Time: {$responseTime}ms";

                    // Add response body summary if detailed
                    $body = $response->json();
                    if (is_array($body)) {
                        if (isset($body['data']) && is_array($body['data'])) {
                            $details[] = "  Response: " . count($body['data']) . " items returned";
                        } else {
                            $details[] = "  Response: Valid JSON returned";
                        }
                    }
                }
            } catch (\Exception $e) {
                $issues[] = "Error checking endpoint {$endpoint}: " . $e->getMessage();
            }
        }

        // List all available API routes if detailed
        if ($detailed) {
            $apiRoutes = collect(Route::getRoutes())->filter(function ($route) {
                return strpos($route->uri, 'api/') === 0;
            })->map(function ($route) {
                return [
                    'methods' => implode('|', $route->methods),
                    'uri' => $route->uri,
                    'name' => $route->getName(),
                ];
            })->values();

            $details[] = "Available API Routes: " . $apiRoutes->count();

            foreach ($apiRoutes as $route) {
                $details[] = "  [{$route['methods']}] {$route['uri']}";
            }
        }

        return [
            'success' => empty($issues),
            'issues' => $issues,
            'details' => $details,
        ];
    }

    /**
     * Get the name of the health check.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'API Endpoints';
    }
}
