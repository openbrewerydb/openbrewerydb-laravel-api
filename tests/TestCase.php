<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @method \Illuminate\Testing\TestResponse assertJsonApiResponse(\Illuminate\Testing\TestResponse $response)
 * @method \Illuminate\Testing\TestResponse assertJsonApiStructure(\Illuminate\Testing\TestResponse $response)
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Indicates whether the default seeder should run before each test.
     */
    protected bool $seed = false;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Any global test setup can go here
    }

    /**
     * Reset the application state between tests.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
