<?php

namespace App\Console\Commands\HealthCheck\Fixers;

use App\Console\Commands\HealthCheck\HealthFixerInterface;
use Illuminate\Console\Command;

class SearchHealthFixer implements HealthFixerInterface
{
    /**
     * The console command instance.
     *
     * @var \Illuminate\Console\Command
     */
    protected $command;

    /**
     * Create a new search health fixer instance.
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

        // Fix search index issues
        try {
            $this->command->callSilent('emergency:search-repair', ['--force' => true, '--recreate-index' => true]);
            $fixedIssues[] = 'Repaired search indexes';
        } catch (\Exception $e) {
            // Repair failed
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
        return 'Search';
    }
}
