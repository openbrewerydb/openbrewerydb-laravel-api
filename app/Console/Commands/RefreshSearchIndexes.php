<?php

namespace App\Console\Commands;

use App\Models\Brewery;
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
        $this->call('scout:flush', ['model' => Brewery::class]);

        $this->call('scout:import', ['model' => Brewery::class]);

        $this->newLine();

        $this->info('Search indexes refreshed successfully.');
    }
}
