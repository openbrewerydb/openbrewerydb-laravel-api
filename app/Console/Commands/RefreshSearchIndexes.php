<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RefreshSearchIndexes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-search-indexes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the search indexes for the application.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('scout:flush', ['model' => 'App\Models\Brewery']);

        $this->call('scout:import', ['model' => 'App\Models\Brewery']);

        $this->info('Search indexes refreshed successfully.');
    }
}
