<?php

namespace App\Console\Commands\HealthCheck\Checkers;

use App\Console\Commands\HealthCheck\HealthCheckerInterface;
use Illuminate\Support\Facades\App;

class EnvironmentHealthChecker implements HealthCheckerInterface
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

        // Check PHP version
        $phpVersion = phpversion();
        $requiredPhpVersion = '8.1.0';
        if (version_compare($phpVersion, $requiredPhpVersion, '<')) {
            $issues[] = "PHP version {$phpVersion} is below the required version {$requiredPhpVersion}";
        }

        if ($detailed) {
            $details[] = "PHP Version: {$phpVersion}";
            $details[] = 'Laravel Version: '.app()->version();
            $details[] = 'Environment: '.App::environment();
            $details[] = 'Debug Mode: '.(config('app.debug') ? 'Enabled' : 'Disabled');
            $details[] = 'Cache Driver: '.config('cache.default');
            $details[] = 'Queue Connection: '.config('queue.default');
            $details[] = 'Database Connection: '.config('database.default');
            $details[] = 'Scout Driver: '.config('scout.driver');

            // Add server information
            $details[] = 'Server Software: '.($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown');
            $details[] = 'Server OS: '.php_uname('s').' '.php_uname('r');

            // Add memory information
            $memoryLimit = ini_get('memory_limit');
            $details[] = "Memory Limit: {$memoryLimit}";

            // Add timezone information
            $details[] = 'Timezone: '.config('app.timezone');
            $details[] = 'Current Time: '.now()->format('Y-m-d H:i:s');
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
        return 'Environment';
    }
}
