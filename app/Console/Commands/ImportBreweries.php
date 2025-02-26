<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
    public function handle(): void
    {
        $this->info('Starting brewery import...');

        $this->newLine();

        DB::raw('TRUNCATE TABLE breweries');

        $json = file_get_contents(
            filename: 'https://raw.githubusercontent.com/openbrewerydb/openbrewerydb/refs/heads/master/breweries.json',
        );

        $data = collect(json_decode(json: $json, associative: true));

        $bar = $this->output->createProgressBar(floor($data->count() / 100));

        $data
            ->chunk(100)
            ->each(function ($chunk) use ($bar) {
                DB::table('breweries')->insertOrIgnore(
                    $chunk->map(function ($brewery) {
                        return [
                            'id' => $brewery['id'],
                            'name' => $brewery['name'],
                            'brewery_type' => $brewery['brewery_type'],
                            'address_1' => $brewery['address_1'],
                            'address_2' => $brewery['address_2'],
                            'address_3' => $brewery['address_3'],
                            'city' => $brewery['city'],
                            'state_province' => $brewery['state_province'],
                            'country' => $brewery['country'],
                            'postal_code' => $brewery['postal_code'],
                            'website_url' => $brewery['website_url'],
                            'phone' => $brewery['phone'],
                            'latitude' => $brewery['latitude'],
                            'longitude' => $brewery['longitude'],
                        ];
                    })->toArray(),
                );

                $bar->advance();
            });

        $bar->finish();

        $this->newLine();

        $this->info('Completed importing breweries!');
    }
}
