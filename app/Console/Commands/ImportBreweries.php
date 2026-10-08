<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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
     * The URL of the upstream Open Brewery DB dataset.
     */
    private const SOURCE_URL = 'https://raw.githubusercontent.com/openbrewerydb/openbrewerydb/refs/heads/master/breweries.json';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting brewery import...');

        $this->newLine();

        try {
            $breweries = Http::connectTimeout(10)
                ->timeout(60)
                ->retry(3, 1000)
                ->get(self::SOURCE_URL)
                ->collect();
        } catch (RequestException|ConnectionException $e) {
            $this->fail('Unable to download the brewery dataset: '.$e->getMessage());
        }

        $bar = $this->output->createProgressBar((int) ceil($breweries->count() / 100));

        DB::transaction(function () use ($breweries, $bar) {
            DB::table('breweries')->delete();

            $breweries
                ->chunk(100)
                ->each(function (Collection $chunk) use ($bar) {
                    DB::table('breweries')->insertOrIgnore(
                        $chunk->map(function (array $brewery) {
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
        });

        $bar->finish();

        $this->newLine();

        $this->info('Completed importing breweries!');

        return self::SUCCESS;
    }
}
