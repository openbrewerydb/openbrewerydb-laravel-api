<?php

namespace App\Jobs\Import;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class Breweries implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::raw('TRUNCATE TABLE breweries');

        $json = file_get_contents(
            filename: 'https://raw.githubusercontent.com/openbrewerydb/openbrewerydb/refs/heads/master/breweries.json',
        );

        $data = collect(json_decode(json: $json, associative: true));

        $data
            ->chunk(100)
            ->each(function ($chunk) {
                DB::table('breweries')->insertOrIgnore(
                    $chunk->map(function ($brewery) {
                        return [
                            'id' => $brewery['id'],
                            'name' => $brewery['name'],
                            'type' => $brewery['brewery_type'],
                            'address_1' => $brewery['address_1'],
                            'address_2' => $brewery['address_2'],
                            'address_3' => $brewery['address_3'],
                            'city' => $brewery['city'],
                            'province' => $brewery['state_province'],
                            'country' => $brewery['country'],
                            'postal_code' => $brewery['postal_code'],
                            'website_url' => $brewery['website_url'],
                            'phone_number' => $brewery['phone'],
                            'latitude' => $brewery['latitude'],
                            'longitude' => $brewery['longitude'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    })->toArray(),
                );
            });
    }
}
