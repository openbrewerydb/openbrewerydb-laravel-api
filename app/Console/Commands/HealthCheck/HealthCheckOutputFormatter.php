<?php

namespace App\Console\Commands\HealthCheck;

use Illuminate\Console\Command;

class HealthCheckOutputFormatter
{
    /**
     * Format health check results based on the specified format.
     *
     * @param  array  $results  The health check results
     * @param  array  $issues  All issues found
     * @param  array  $fixedIssues  Issues that were fixed
     * @param  string  $format  The output format (cli, json)
     * @param  Command|null  $command  The command instance for CLI output
     * @return mixed  The formatted output
     */
    public static function format(
        array $results, 
        array $issues, 
        array $fixedIssues, 
        string $format = 'cli', 
        ?Command $command = null
    ) {
        if ($format === 'json') {
            return self::formatJson($results, $issues, $fixedIssues);
        }
        
        return self::formatCli($results, $issues, $fixedIssues, $command);
    }
    
    /**
     * Format results as JSON.
     *
     * @param  array  $results  The health check results
     * @param  array  $issues  All issues found
     * @param  array  $fixedIssues  Issues that were fixed
     * @return string  JSON formatted output
     */
    protected static function formatJson(array $results, array $issues, array $fixedIssues): string
    {
        $output = [
            'timestamp' => now()->toIso8601String(),
            'status' => empty($issues) ? 'healthy' : 'issues_detected',
            'issues_count' => count($issues),
            'fixed_count' => count($fixedIssues),
            'checks' => $results,
            'issues' => $issues,
            'fixed' => $fixedIssues,
        ];

        return json_encode($output, JSON_PRETTY_PRINT);
    }
    
    /**
     * Format results for CLI output.
     *
     * @param  array  $results  The health check results
     * @param  array  $issues  All issues found
     * @param  array  $fixedIssues  Issues that were fixed
     * @param  Command|null  $command  The command instance
     * @return bool  Success status
     */
    protected static function formatCli(array $results, array $issues, array $fixedIssues, ?Command $command): bool
    {
        if (!$command) {
            return empty($issues);
        }
        
        if (empty($issues)) {
            $command->info('System health check completed. No issues found!');
        } else {
            $command->error('System health check completed. Issues found:');

            foreach ($issues as $issue) {
                $command->line(" - {$issue}");
            }

            if (!empty($fixedIssues)) {
                $command->newLine();
                $command->info('The following issues were fixed:');

                foreach ($fixedIssues as $fixed) {
                    $command->line(" - {$fixed}");
                }
            }

            if (count($issues) > count($fixedIssues)) {
                $command->newLine();
                $command->warn('Some issues could not be automatically fixed. Consider running:');
                $command->line(' - php artisan emergency:db-reset --force');
                $command->line(' - php artisan emergency:search-repair --force --recreate-index');
            }
        }
        
        return empty($issues) || !empty($fixedIssues);
    }
}
