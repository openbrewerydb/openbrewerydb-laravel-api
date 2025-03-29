<?php

namespace App\Console\Commands\HealthCheck\Checkers;

use App\Console\Commands\HealthCheck\BaseChecker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class DatabaseHealthChecker extends BaseChecker
{
    /**
     * Implement the actual health check logic.
     *
     * @param  bool  $detailed  Whether to show detailed information
     * @return void
     */
    protected function runCheck(bool $detailed): void
    {
        // Check 1: Database file existence
        $dbPath = config('database.connections.sqlite.database');
        $dbExists = File::exists($dbPath);

        if (! $dbExists) {
            $this->addIssue("Database file not found at {$dbPath}");
            return; // Stop further checks if database doesn't exist
        }

        // Check 2: Database file permissions
        $isWritable = is_writable($dbPath);
        if (! $isWritable) {
            $this->addIssue('Database file is not writable');
        }

        // Check 3: Database structure
        try {
            $hasBreweriesTable = Schema::hasTable('breweries');
            if (! $hasBreweriesTable) {
                $this->addIssue('Breweries table does not exist in the database');
            }
        } catch (\Exception $e) {
            $this->addIssue('Error checking database structure: ' . $e->getMessage());
        }

        // Check 4: Database data
        try {
            $breweriesCount = DB::table('breweries')->count();
            if ($breweriesCount === 0) {
                $this->addIssue('No breweries found in the database');
            }

            if ($detailed) {
                $this->addDetail("Found {$breweriesCount} breweries in the database");
            }
        } catch (\Exception $e) {
            $this->addIssue('Error checking database data: ' . $e->getMessage());
        }
    }

    /**
     * Get the name of the health check.
     */
    public function getName(): string
    {
        return 'Database';
    }
}
