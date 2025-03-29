<?php

namespace App\Console\Commands\HealthCheck\Traits;

use Illuminate\Support\Facades\Http;

trait ApiTestingTrait
{
    /**
     * Test an API endpoint and measure response time.
     *
     * @param  string  $url  The endpoint URL to test
     * @param  string  $method  The HTTP method to use (GET, POST, etc.)
     * @param  int  $maxResponseTime  Maximum acceptable response time in milliseconds
     * @return array  Test results including success, status, time, and response
     */
    protected function testEndpoint(string $url, string $method = 'GET', int $maxResponseTime = 2000): array
    {
        try {
            $startTime = microtime(true);
            
            $response = Http::timeout(5)->withHeaders([
                'Accept' => 'application/json',
            ])->$method($url);
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000); // in milliseconds
            
            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'time' => $responseTime,
                'response' => $response,
                'tooSlow' => $responseTime > $maxResponseTime,
                'body' => $response->json(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Format response body summary for detailed output.
     *
     * @param  array|null  $body  The JSON response body
     * @return string  A summary of the response body
     */
    protected function formatResponseSummary($body): string
    {
        if (!is_array($body)) {
            return 'Invalid or empty response';
        }
        
        if (isset($body['data']) && is_array($body['data'])) {
            return count($body['data']) . ' items returned';
        }
        
        return 'Valid JSON returned';
    }
}
