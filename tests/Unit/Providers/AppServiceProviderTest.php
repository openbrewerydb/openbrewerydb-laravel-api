<?php

namespace Tests\Unit\Providers;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    // Reset URL::forceScheme
    URL::forceScheme(null);
});

test('forces https in production environment', function () {
    // Set environment to production
    app()['env'] = 'production';

    // Create and boot the service provider
    $provider = new AppServiceProvider(app());
    $provider->boot();

    // Assert HTTPS is forced
    expect(URL::formatScheme())->toBe('https://');
});

test('forces https when APP_FORCE_HTTPS is true', function () {
    // Set environment variable
    config(['app.force_https' => true]);

    // Create and boot the service provider
    $provider = new AppServiceProvider(app());
    $provider->boot();

    // Assert HTTPS is forced
    expect(URL::formatScheme())->toBe('https://');
});

test('does not force https in local environment by default', function () {
    // Set environment to local
    app()['env'] = 'local';
    config(['app.force_https' => false]);

    // Create and boot the service provider
    $provider = new AppServiceProvider(app());
    $provider->boot();

    // Assert HTTPS is not forced
    expect(URL::formatScheme())->toBe('http://');
});

test('does not force https when APP_FORCE_HTTPS is false', function () {
    // Set environment variable
    config(['app.force_https' => false]);

    // Create and boot the service provider
    $provider = new AppServiceProvider(app());
    $provider->boot();

    // Assert HTTPS is not forced
    expect(URL::formatScheme())->toBe('http://');
});
