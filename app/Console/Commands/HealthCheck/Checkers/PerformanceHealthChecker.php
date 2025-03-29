<?php

namespace App\Console\Commands\HealthCheck\Checkers;

use App\Console\Commands\HealthCheck\HealthCheckerInterface;
use App\Models\Brewery;
use Illuminate\Support\Facades\DB;

class PerformanceHealthChecker implements HealthCheckerInterface
{
    /**
     * Maximum acceptable query time in milliseconds.
     *
     * @var int
     */
    protected $maxQueryTime = 200;

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
        
        // Check database query performance
        try {
            $startTime = microtime(true);
            
            // Run a simple query to check database performance
            $result = DB::table('breweries')->limit(10)->get();
            
            $endTime = microtime(true);
            $queryTime = round(($endTime - $startTime) * 1000); // in milliseconds
            
            if ($queryTime > $this->maxQueryTime) {
                $issues[] = "Database query time ({$queryTime}ms) exceeds maximum ({$this->maxQueryTime}ms)";
            }
            
            if ($detailed) {
                $details[] = "Database Query Time: {$queryTime}ms";
            }
        } catch (\Exception $e) {
            $issues[] = "Error checking database performance: " . $e->getMessage();
        }
        
        // Check search performance
        try {
            $startTime = microtime(true);
            
            // Run a simple search query to check Meilisearch performance
            $searchResults = Brewery::search('brewery')->take(10)->get();
            
            $endTime = microtime(true);
            $searchTime = round(($endTime - $startTime) * 1000); // in milliseconds
            
            // Search should typically be fast, but can be slower than DB queries
            $maxSearchTime = $this->maxQueryTime * 2;
            if ($searchTime > $maxSearchTime) {
                $issues[] = "Search query time ({$searchTime}ms) exceeds maximum ({$maxSearchTime}ms)";
            }
            
            if ($detailed) {
                $details[] = "Search Query Time: {$searchTime}ms";
                $details[] = "Search Results Count: " . $searchResults->count();
            }
        } catch (\Exception $e) {
            $issues[] = "Error checking search performance: " . $e->getMessage();
        }
        
        // Check system load
        try {
            if (function_exists('sys_getloadavg')) {
                $load = sys_getloadavg();
                $currentLoad = $load[0];
                
                // Get number of CPU cores
                $cpuCores = 1;
                if (is_readable('/proc/cpuinfo')) {
                    $cpuinfo = file_get_contents('/proc/cpuinfo');
                    preg_match_all('/^processor/m', $cpuinfo, $matches);
                    $cpuCores = count($matches[0]);
                }
                
                // Load per core should ideally be under 0.7
                $loadPerCore = $currentLoad / $cpuCores;
                if ($loadPerCore > 0.7) {
                    $issues[] = "System load ({$currentLoad} / {$cpuCores} cores = {$loadPerCore}) is high";
                }
                
                if ($detailed) {
                    $details[] = "System Load: {$load[0]} (1 min), {$load[1]} (5 min), {$load[2]} (15 min)";
                    $details[] = "CPU Cores: {$cpuCores}";
                    $details[] = "Load Per Core: " . round($loadPerCore, 2);
                }
            }
        } catch (\Exception $e) {
            // System load check is non-critical, so just add to details
            if ($detailed) {
                $details[] = "Could not check system load: " . $e->getMessage();
            }
        }
        
        // Check memory usage
        try {
            $memoryUsage = memory_get_usage(true);
            $memoryLimit = $this->getMemoryLimitInBytes();
            
            $memoryUsagePercentage = ($memoryUsage / $memoryLimit) * 100;
            
            if ($memoryUsagePercentage > 70) {
                $issues[] = "Memory usage is high: " . round($memoryUsagePercentage, 2) . "% of limit";
            }
            
            if ($detailed) {
                $details[] = "Memory Usage: " . $this->formatBytes($memoryUsage) . " / " . 
                            $this->formatBytes($memoryLimit) . " (" . 
                            round($memoryUsagePercentage, 2) . "%)";
            }
        } catch (\Exception $e) {
            // Memory check is non-critical
            if ($detailed) {
                $details[] = "Could not check memory usage: " . $e->getMessage();
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
        return 'Performance';
    }
    
    /**
     * Format bytes to human-readable format.
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    protected function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * Get PHP memory limit in bytes.
     *
     * @return int
     */
    protected function getMemoryLimitInBytes(): int
    {
        $memoryLimit = ini_get('memory_limit');
        
        // If no limit is set, return a high value
        if ($memoryLimit === '-1') {
            return PHP_INT_MAX;
        }
        
        $unit = strtoupper(substr($memoryLimit, -1));
        $value = (int) substr($memoryLimit, 0, -1);
        
        switch ($unit) {
            case 'G':
                $value *= 1024;
                // no break
            case 'M':
                $value *= 1024;
                // no break
            case 'K':
                $value *= 1024;
        }
        
        return $value;
    }
}
