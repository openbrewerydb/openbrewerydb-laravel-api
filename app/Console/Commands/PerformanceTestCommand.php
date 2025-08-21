<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PerformanceTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'perf:gauntlet {--iterations=20}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs a gauntlet of performance tests on the /v1/breweries/meta endpoint.';

    /**
     * The test scenarios to run.
     *
     * @var array
     */
    private array $scenarios = [
        'by_city' => 'san',
        'by_name' => 'dog',
        'by_state' => 'California',
        'by_postal' => '92124',
        'by_country' => 'United+States', // Use URL-encoded value
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $iterations = (int) $this->option('iterations');

        $this->runIndividualTests($iterations);
        $this->runCumulativeTests($iterations);

        return 0;
    }

    /**
     * Run tests for each filter individually.
     */
    private function runIndividualTests(int $iterations): void
    {
        $this->info("\n--- 🚀 Running Individual Filter Tests ({$iterations} iterations each) ---");
        $results = [];

        foreach ($this->scenarios as $filter => $value) {
            $this->output->write("Testing filter '{$filter}'...");
            $times = $this->runTest("{$filter}={$value}", $iterations);

            if (empty($times)) {
                $this->output->writeln(' <error>FAIL</error>');
                continue;
            }
            $this->output->writeln(' <info>OK</info>');

            $results[] = [
                'filter' => $filter,
                'avg' => number_format(array_sum($times) / count($times), 2),
                'min' => number_format(min($times), 2),
                'max' => number_format(max($times), 2),
            ];
        }

        $this->table(
            ['Filter', 'Avg (ms)', 'Min (ms)', 'Max (ms)'],
            $results
        );
    }

    /**
     * Run tests with filters being added cumulatively.
     */
    private function runCumulativeTests(int $iterations): void
    {
        $this->info("\n--- 🚀 Running Cumulative Filter Tests ({$iterations} iterations each) ---");
        $results = [];
        $queryParams = [];

        foreach ($this->scenarios as $filter => $value) {
            $queryParams[$filter] = $value;
            $queryString = http_build_query($queryParams);
            $activeFilters = implode(', ', array_keys($queryParams));

            $this->output->write("Testing filters: {$activeFilters}...");
            $times = $this->runTest($queryString, $iterations);

            if (empty($times)) {
                $this->output->writeln(' <error>FAIL</error>');
                continue;
            }
            $this->output->writeln(' <info>OK</info>');

            $results[] = [
                'filters' => $activeFilters,
                'avg' => number_format(array_sum($times) / count($times), 2),
                'min' => number_format(min($times), 2),
                'max' => number_format(max($times), 2),
            ];
        }

        $this->table(
            ['Active Filters', 'Avg (ms)', 'Min (ms)', 'Max (ms)'],
            $results
        );
    }

    /**
     * Run the actual HTTP requests and measure response times.
     */
    private function runTest(string $queryString, int $iterations): array
    {
        $times = [];

        // IMPORTANT: This is the correct URL for the API endpoint
        $baseUrl = config('app.url') . '/v1/breweries/meta';
        $url = $baseUrl . '?' . $queryString;

        // Add debugging output for the URL
        if ($this->getOutput()->isVerbose()) {
            $this->line("  -> Testing URL: {$url}");
        }

        for ($i = 0; $i < $iterations; $i++) {
            $startTime = microtime(true);
            try {
                $response = Http::get($url);
                if (!$response->successful()) {
                    Log::error('Perf Test Failed Response', ['url' => $url, 'status' => $response->status(), 'body' => $response->body()]);

                    return []; // Stop this test on first failure
                }
            } catch (\Exception $e) {
                Log::error('Perf Test Exception', ['url' => $url, 'message' => $e->getMessage()]);

                return []; // Stop this test on first failure
            }

            $endTime = microtime(true);
            $times[] = ($endTime - $startTime) * 1000; // in milliseconds
        }

        return $times;
    }
}
