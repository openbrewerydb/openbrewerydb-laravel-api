<?php

namespace App\Console\Commands\HealthCheck\Fixers;

use App\Console\Commands\HealthCheck\HealthFixerInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class DatabaseHealthFixer implements HealthFixerInterface
{
    /**
     * The console command instance.
     *
     * @var \Illuminate\Console\Command
     */
    protected $command;

    /**
     * Create a new database health fixer instance.
     *
     * @return void
     */
    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * Fix the health issues.
     *
     * @param  array  $issues  The issues to fix
     * @return array Array with success status and fixed issues
     */
    public function fix(array $issues): array
    {
        $fixedIssues = [];
        $dbPath = config('database.connections.sqlite.database');
        $dbExists = File::exists($dbPath);

        // Fix 1: Missing database file
        if (! $dbExists) {
            $dbDir = dirname($dbPath);
            if (! File::exists($dbDir)) {
                File::makeDirectory($dbDir, 0755, true);
            }

            if (touch($dbPath)) {
                $fixedIssues[] = 'Created missing database file';
            }
        }

        // Fix 2: Database file permissions
        if ($dbExists && ! is_writable($dbPath)) {
            if (chmod($dbPath, 0664)) {
                $fixedIssues[] = 'Fixed database file permissions';
            }
        }

        // Fix 3: Missing database structure or data
        $needsMigration = ! $dbExists ||
                          (Schema::hasTable('breweries') === false) ||
                          (DB::table('breweries')->count() === 0);

        if ($needsMigration) {
            try {
                $this->command->callSilent('migrate:fresh', ['--force' => true]);
                $fixedIssues[] = 'Recreated database structure';
            } catch (\Exception $e) {
                // Migration failed
            }

            try {
                $this->command->callSilent('app:import-breweries');
                $fixedIssues[] = 'Imported breweries data';
            } catch (\Exception $e) {
                // Import failed
            }
        }

        return [
            'success' => ! empty($fixedIssues),
            'fixed' => $fixedIssues,
        ];
    }

    /**
     * Get the name of the health fixer.
     */
    public function getName(): string
    {
        return 'Database';
    }
}
