<?php

namespace App\Console\Commands\HealthCheck\Checkers;

use App\Console\Commands\HealthCheck\HealthCheckerInterface;
use Illuminate\Support\Facades\Storage;

class DiskSpaceHealthChecker implements HealthCheckerInterface
{
    /**
     * Minimum free disk space in percentage.
     *
     * @var int
     */
    protected $minFreeSpacePercentage = 5;

    /**
     * Run the health check.
     *
     * @param bool $detailed Whether to show detailed information
     * @return array Array with success status and any issues found
     */
    public function check(bool $detailed = false): array
    {
        $issues = [];
        $details = [];
        
        // Check storage disk space
        $disks = ['local', 'public'];
        
        foreach ($disks as $disk) {
            if (!config("filesystems.disks.{$disk}")) {
                continue;
            }
            
            try {
                $diskPath = Storage::disk($disk)->path('');
                $totalSpace = disk_total_space($diskPath);
                $freeSpace = disk_free_space($diskPath);
                $usedSpace = $totalSpace - $freeSpace;
                
                $freeSpacePercentage = round(($freeSpace / $totalSpace) * 100, 2);
                
                if ($freeSpacePercentage < $this->minFreeSpacePercentage) {
                    $issues[] = "Disk '{$disk}' has only {$freeSpacePercentage}% free space, minimum required is {$this->minFreeSpacePercentage}%";
                }
                
                if ($detailed) {
                    $details[] = "Disk '{$disk}' - Total: " . $this->formatBytes($totalSpace) . 
                                ", Used: " . $this->formatBytes($usedSpace) . 
                                " (" . round(($usedSpace / $totalSpace) * 100, 2) . "%), " .
                                "Free: " . $this->formatBytes($freeSpace) . 
                                " (" . $freeSpacePercentage . "%)";
                }
            } catch (\Exception $e) {
                $issues[] = "Error checking disk '{$disk}': " . $e->getMessage();
            }
        }
        
        // Check storage for application logs
        try {
            $logPath = storage_path('logs');
            if (file_exists($logPath)) {
                $logSize = $this->dirSize($logPath);
                
                // Warn if logs are over 100MB
                if ($logSize > 100 * 1024 * 1024) {
                    $issues[] = "Log directory size is " . $this->formatBytes($logSize) . ", consider cleaning up logs";
                }
                
                if ($detailed) {
                    $details[] = "Log Directory Size: " . $this->formatBytes($logSize);
                }
            }
        } catch (\Exception $e) {
            $issues[] = "Error checking log directory: " . $e->getMessage();
        }
        
        return [
            'success' => empty($issues),
            'issues' => $issues,
            'details' => $details,
        ];
    }
    
    /**
     * Get the name of the health check.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Disk Space';
    }
    
    /**
     * Format bytes to human-readable format.
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    protected function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * Get directory size recursively.
     *
     * @param string $dir
     * @return int
     */
    protected function dirSize($dir): int
    {
        $size = 0;
        
        foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : $this->dirSize($each);
        }
        
        return $size;
    }
}
