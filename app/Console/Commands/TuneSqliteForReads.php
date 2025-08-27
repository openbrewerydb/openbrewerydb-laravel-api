<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TuneSqliteForReads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:tune-sqlite-reads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tune SQLite database for improved read performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (config('database.default') !== 'sqlite') {
            $this->error('This command is only available for SQLite databases.');

            return 1;
        }

        $this->info('Tuning SQLite database for read performance...');

        try {
            // Enable WAL mode for better read performance
            DB::statement('PRAGMA journal_mode=WAL');
            $this->info('✓ Journal mode set to WAL (Write-Ahead Logging)');

            // Set cache size to 10,000 pages (approximately 40MB with default page size of 4KB)
            DB::statement('PRAGMA cache_size=10000');
            $this->info('✓ Cache size set to 10,000 pages');

            // Additional read optimizations
            DB::statement('PRAGMA synchronous=NORMAL');
            $this->info('✓ Synchronous mode set to NORMAL');

            DB::statement('PRAGMA temp_store=MEMORY');
            $this->info('✓ Temporary tables stored in memory');

            DB::statement('PRAGMA mmap_size=268435456'); // 256MB
            $this->info('✓ Memory-mapped I/O enabled (256MB)');

            // Verify the settings
            $this->newLine();
            $this->info('Current SQLite configuration:');

            $journalMode = DB::selectOne('PRAGMA journal_mode');
            $this->line("Journal mode: {$journalMode->journal_mode}");

            $cacheSize = DB::selectOne('PRAGMA cache_size');
            $this->line("Cache size: {$cacheSize->cache_size} pages");

            $synchronous = DB::selectOne('PRAGMA synchronous');
            $this->line("Synchronous: {$synchronous->synchronous}");

            $tempStore = DB::selectOne('PRAGMA temp_store');
            $this->line("Temp store: {$tempStore->temp_store}");

            $mmapSize = DB::selectOne('PRAGMA mmap_size');
            $this->line('Memory-mapped size: '.number_format($mmapSize->mmap_size / 1024 / 1024, 1).'MB');

            $this->newLine();
            $this->info('SQLite database has been successfully tuned for read performance!');
            $this->comment('Note: These settings are applied immediately. For persistent configuration across');
            $this->comment('application restarts, set the following in your .env file:');
            $this->comment('DB_JOURNAL_MODE=WAL');
            $this->comment('DB_CACHE_SIZE=10000');
            $this->comment('DB_SYNCHRONOUS=NORMAL');
            $this->comment('DB_TEMP_STORE=MEMORY');
            $this->comment('DB_MMAP_SIZE=268435456');

        } catch (\Exception $e) {
            $this->error('Failed to tune SQLite database: '.$e->getMessage());

            return 1;
        }

        return 0;
    }
}
