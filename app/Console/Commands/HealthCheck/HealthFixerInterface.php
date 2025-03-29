<?php

namespace App\Console\Commands\HealthCheck;

interface HealthFixerInterface
{
    /**
     * Fix the health issues.
     *
     * @param array $issues The issues to fix
     * @return array Array with success status and fixed issues
     */
    public function fix(array $issues): array;
    
    /**
     * Get the name of the health fixer.
     *
     * @return string
     */
    public function getName(): string;
}
