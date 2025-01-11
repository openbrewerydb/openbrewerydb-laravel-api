<?php

namespace App\Console\Commands;

use App\Jobs\Import\Breweries;
use Illuminate\Console\Command;

class ImportBreweries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-breweries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import breweries from the Open Brewery DB API GitHub repository.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Breweries::dispatch();

        $this->info('Dispatched import breweries job!');
    }
}
