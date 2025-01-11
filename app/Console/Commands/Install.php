<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the application.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->option('force')) {
            $this->newLine(2);

            $this->info("Running the install will reset all of the application's data.");

            if (! $this->confirm('Do you wish to continue?')) {
                $this->fail('Application Install cancelled.');
            }
        }

        $this->info('Starting to install the application...');

        $this->ensureDatabaseExists();

        $this->copyEnvExample();

        $this->createAppKey();

        $this->migrateDatabase();

        $this->info('Application installed successfully.');
    }

    /**
     * Ensure the SQLite database exists.
     */
    protected function ensureDatabaseExists(): void
    {
        file_exists(database_path('database.sqlite')) || touch(database_path('database.sqlite'));
    }

    /**
     * Copy the .env.example file to .env.
     */
    protected function copyEnvExample(): void
    {
        if (! file_exists(base_path('.env'))) {
            copy(base_path('.env.example'), base_path('.env'));
        }
    }

    /**
     * Create APP_KEY in the .env file.
     */
    protected function createAppKey(): void
    {
        if (filled(config('app.key'))) {
            return;
        }

        try {
            $this->call('key:generate', [
                '--ansi' => true,
            ]);
        } catch (\Throwable $th) {
            $this->fail('❌ There was an issue creating the application key, check the logs.');
        }
    }

    /**
     * Migrate the database.
     */
    protected function migrateDatabase(): void
    {
        try {
            $this->call('migrate:fresh', [
                '--force' => true,
            ]);
        } catch (\Throwable $th) {
            $this->fail('❌ There was an issue migrating the database, check the logs.');
        }
    }
}
