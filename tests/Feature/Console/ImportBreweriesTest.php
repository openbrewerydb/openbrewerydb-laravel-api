<?php

use App\Models\Brewery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

const BREWERY_DATASET_URL = 'https://raw.githubusercontent.com/openbrewerydb/openbrewerydb/refs/heads/master/breweries.json';

test('import replaces existing breweries with the upstream dataset', function () {
    Http::preventStrayRequests();
    $removedBrewery = createBrewery(['name' => 'Long Gone Brewing']);
    $renamedBrewery = createBrewery(['name' => 'Old Name Brewing']);
    $newBreweryId = fake()->uuid();
    Http::fake([
        BREWERY_DATASET_URL => Http::response([
            Brewery::factory()->raw(['id' => $renamedBrewery->id, 'name' => 'New Name Brewing']),
            Brewery::factory()->raw(['id' => $newBreweryId, 'name' => 'Brand New Brewing']),
        ]),
    ]);

    $this->artisan('app:import-breweries')->assertSuccessful();

    $this->assertModelMissing($removedBrewery);
    $this->assertDatabaseHas('breweries', ['id' => $renamedBrewery->id, 'name' => 'New Name Brewing']);
    $this->assertDatabaseHas('breweries', ['id' => $newBreweryId, 'name' => 'Brand New Brewing']);
    $this->assertDatabaseCount('breweries', 2);
});

test('import fails and keeps existing breweries when the dataset cannot be downloaded', function () {
    Http::preventStrayRequests();
    Sleep::fake();
    $existingBrewery = createBrewery();
    Http::fake([
        BREWERY_DATASET_URL => Http::response(status: 500),
    ]);

    $this->artisan('app:import-breweries')->assertFailed();

    $this->assertModelExists($existingBrewery);
    Http::assertSentCount(3);
});
