<?php

namespace App\Console\Commands;

use App\Console\Commands\HealthCheck\Checkers\DatabaseHealthChecker;
use App\Console\Commands\HealthCheck\Checkers\SearchHealthChecker;
use App\Console\Commands\HealthCheck\Fixers\DatabaseHealthFixer;
use App\Console\Commands\HealthCheck\Fixers\SearchHealthFixer;
use Illuminate\Console\Command;
use Laravel\Scout\EngineManager;

class SystemHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:health-check 
                            {--fix : Attempt to automatically fix issues}
                            {--detailed : Show detailed information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the health of the system and identify potential issues';

    /**
     * The health checkers.
     *
     * @var array
     */
    protected $checkers = [];

    /**
     * The health fixers.
     *
     * @var array
     */
    protected $fixers = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(EngineManager $engineManager)
    {
        $this->newLine();
        $this->components->info('Starting system health check...');
        $this->newLine();

        // Initialize checkers and fixers
        $this->initializeCheckersAndFixers();

        $allIssues = [];
        $allFixedIssues = [];

        // Run all health checks
        foreach ($this->checkers as $checker) {
            $checkName = $checker->getName();
            
            $this->components->task("Checking {$checkName}", function () use ($checker, &$allIssues) {
                $result = $checker->check($this->option('detailed'));
                
                if (!$result['success']) {
                    $allIssues = array_merge($allIssues, $result['issues']);
                    
                    return false;
                }
                
                // Display detailed information if available
                if (!empty($result['details']) && $this->option('detailed')) {
                    foreach ($result['details'] as $detail) {
                        $this->info("  {$detail}");
                    }
                }
                
                return true;
            });
        }

        // Fix issues if requested
        if ($this->option('fix') && !empty($allIssues)) {
            $this->newLine();
            $this->components->info('Attempting to fix issues...');

            foreach ($this->fixers as $fixer) {
                $fixerName = $fixer->getName();
                
                $this->components->task("Fixing {$fixerName} issues", function () use ($fixer, $allIssues, &$allFixedIssues) {
                    $result = $fixer->fix($allIssues);
                    
                    if ($result['success']) {
                        $allFixedIssues = array_merge($allFixedIssues, $result['fixed']);
                        
                        return true;
                    }
                    
                    return false;
                });
            }
        }

        $this->newLine();

        if (empty($allIssues)) {
            $this->components->info('System health check completed. No issues found!');
        } else {
            $this->components->error('System health check completed. Issues found:');

            foreach ($allIssues as $issue) {
                $this->line(" - {$issue}");
            }

            if (!empty($allFixedIssues)) {
                $this->newLine();
                $this->components->info('The following issues were fixed:');

                foreach ($allFixedIssues as $fixed) {
                    $this->line(" - {$fixed}");
                }
            }

            if (count($allIssues) > count($allFixedIssues)) {
                $this->newLine();
                $this->components->warn('Some issues could not be automatically fixed. Consider running:');
                $this->line(' - php artisan emergency:db-reset --force');
                $this->line(' - php artisan emergency:search-repair --force --recreate-index');
            }
        }

        return empty($allIssues) || !empty($allFixedIssues) ? 0 : 1;
    }

    /**
     * Initialize the health checkers and fixers.
     *
     * @return void
     */
    protected function initializeCheckersAndFixers()
    {
        // Initialize checkers
        $this->checkers = [
            new DatabaseHealthChecker(),
            new SearchHealthChecker(),
        ];

        // Initialize fixers
        $this->fixers = [
            new DatabaseHealthFixer($this),
            new SearchHealthFixer($this),
        ];
    }
}
