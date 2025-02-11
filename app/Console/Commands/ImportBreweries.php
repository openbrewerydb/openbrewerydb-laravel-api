<?php

namespace App\Console\Commands;

use App\Actions\ImportBreweries as ImportBreweriesAction;
use Illuminate\Console\Command;

class ImportBreweries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-breweries
                            {--async : Import breweries by throwing a job on the queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import breweries from the Open Brewery DB API GitHub repository.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if ($this->option('async')) {
            ImportBreweriesAction::dispatch();

            $this->info('Dispatched import breweries job!');

            return;
        }

        ImportBreweriesAction::run();

        $this->info('Breweries imported!');
    }
}
