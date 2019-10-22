<?php

namespace KonnectIT\LaravelNagiosNrdp\Tests\Integration;

use KonnectIT\LaravelNagiosNrdp\LaravelNagiosNrdpFacade;
use Orchestra\Testbench\TestCase as Orchestra;
use KonnectIT\LaravelNagiosNrdp\LaravelNagiosNrdpServiceProvider;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            LaravelNagiosNrdpServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'NagiosNrdp' => LaravelNagiosNrdpFacade::class,
        ];
    }
}