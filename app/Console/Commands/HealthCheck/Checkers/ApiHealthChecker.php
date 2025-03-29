<?php

namespace App\Console\Commands\HealthCheck\Checkers;

use App\Console\Commands\HealthCheck\BaseChecker;
use App\Console\Commands\HealthCheck\Traits\ApiTestingTrait;
use Illuminate\Support\Facades\Route;

class ApiHealthChecker extends BaseChecker
{
    use ApiTestingTrait;

    /**
     * Maximum acceptable response time in milliseconds.
     *
     * @var int
     */
    protected $maxResponseTime = 2000;

    /**
     * Implement the actual health check logic.
     *
     * @param  bool  $detailed  Whether to show detailed information
     * @return void
     */
    protected function runCheck(bool $detailed): void
    {
        // Get the application URL
        $appUrl = config('app.url');
        if (empty($appUrl)) {
            $this->addIssue('Application URL is not configured');
            return;
        }

        // Check main API endpoints
        $endpoints = [
            '/v1/breweries' => 'GET',
            '/v1/breweries/random' => 'GET',
            '/v1/breweries/search?query=dog' => 'GET',
        ];

        foreach ($endpoints as $endpoint => $method) {
            $url = rtrim($appUrl, '/') . $endpoint;
            $result = $this->testEndpoint($url, $method, $this->maxResponseTime);

            if (isset($result['error'])) {
                $this->addIssue("Error checking endpoint {$endpoint}: {$result['error']}");
                continue;
            }

            if (!$result['success']) {
                $this->addIssue("Endpoint {$endpoint} returned status code {$result['status']}");
            }

            if ($result['tooSlow']) {
                $this->addIssue("Endpoint {$endpoint} response time ({$result['time']}ms) exceeds maximum ({$this->maxResponseTime}ms)");
            }

            if ($detailed) {
                $this->addDetail("Endpoint {$endpoint} - Status: {$result['status']}, Response Time: {$result['time']}ms");
                
                if (isset($result['body'])) {
                    $this->addDetail('  Response: ' . $this->formatResponseSummary($result['body']));
                }
            }
        }

        // List all available API routes if detailed
        if ($detailed) {
            $this->listApiRoutes();
        }
    }

    /**
     * List all available API routes.
     *
     * @return void
     */
    protected function listApiRoutes(): void
    {
        $apiRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return strpos($route->uri, 'api/') === 0;
        })->map(function ($route) {
            return [
                'methods' => implode('|', $route->methods),
                'uri' => $route->uri,
                'name' => $route->getName(),
            ];
        })->values();

        $this->addDetail('Available API Routes: ' . $apiRoutes->count());

        foreach ($apiRoutes as $route) {
            $this->addDetail("  [{$route['methods']}] {$route['uri']}");
        }
    }

    /**
     * Get the name of the health check.
     */
    public function getName(): string
    {
        return 'API Endpoints';
    }
}
