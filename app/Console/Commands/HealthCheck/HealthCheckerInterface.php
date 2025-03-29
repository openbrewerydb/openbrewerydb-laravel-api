<?php

namespace App\Console\Commands\HealthCheck;

interface HealthCheckerInterface
{
    /**
     * Run the health check.
     *
     * @param  bool  $detailed  Whether to show detailed information
     * @return array Array with success status and any issues found
     */
    public function check(bool $detailed = false): array;

    /**
     * Get the name of the health check.
     */
    public function getName(): string;
}
