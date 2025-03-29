<?php

namespace App\Console\Commands\HealthCheck\Checkers;

use App\Console\Commands\HealthCheck\HealthCheckerInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class DatabaseHealthChecker implements HealthCheckerInterface
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

        // Check 1: Database file existence
        $dbPath = config('database.connections.sqlite.database');
        $dbExists = File::exists($dbPath);

        if (! $dbExists) {
            $issues[] = "Database file not found at {$dbPath}";
        }

        // Check 2: Database file permissions
        if ($dbExists) {
            $isWritable = is_writable($dbPath);
            if (! $isWritable) {
                $issues[] = 'Database file is not writable';
            }
        }

        // Check 3: Database structure
        if ($dbExists) {
            try {
                $hasBreweriesTable = Schema::hasTable('breweries');
                if (! $hasBreweriesTable) {
                    $issues[] = 'Breweries table does not exist in the database';
                }
            } catch (\Exception $e) {
                $issues[] = 'Error checking database structure: '.$e->getMessage();
            }
        }

        // Check 4: Database data
        if ($dbExists) {
            try {
                $breweriesCount = DB::table('breweries')->count();
                if ($breweriesCount === 0) {
                    $issues[] = 'No breweries found in the database';
                }

                if ($detailed) {
                    $details[] = "Found {$breweriesCount} breweries in the database";
                }
            } catch (\Exception $e) {
                $issues[] = 'Error checking database data: '.$e->getMessage();
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
     */
    public function getName(): string
    {
        return 'Database';
    }
}
