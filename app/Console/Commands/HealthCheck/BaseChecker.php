<?php

namespace App\Console\Commands\HealthCheck;

abstract class BaseChecker implements HealthCheckerInterface
{
    /**
     * List of issues found during the health check.
     *
     * @var array
     */
    protected $issues = [];

    /**
     * Detailed information about the health check.
     *
     * @var array
     */
    protected $details = [];

    /**
     * Run the health check.
     *
     * @param  bool  $detailed  Whether to show detailed information
     * @return array Array with success status and any issues found
     */
    public function check(bool $detailed = false): array
    {
        // Reset issues and details
        $this->issues = [];
        $this->details = [];
        
        // Run the actual check implementation
        $this->runCheck($detailed);
        
        // Return standardized response format
        return [
            'success' => empty($this->issues),
            'issues' => $this->issues,
            'details' => $this->details,
        ];
    }
    
    /**
     * Implement the actual health check logic.
     *
     * @param  bool  $detailed  Whether to show detailed information
     * @return void
     */
    abstract protected function runCheck(bool $detailed): void;
    
    /**
     * Add an issue to the list of issues.
     *
     * @param  string  $issue  The issue description
     * @return void
     */
    protected function addIssue(string $issue): void
    {
        $this->issues[] = $issue;
    }
    
    /**
     * Add a detail to the list of details.
     *
     * @param  string  $detail  The detail description
     * @return void
     */
    protected function addDetail(string $detail): void
    {
        $this->details[] = $detail;
    }
}
