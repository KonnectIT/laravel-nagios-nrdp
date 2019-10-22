<?php

namespace KonnectIT\LaravelNagiosNrdp\Tests\Integration;

use KonnectIT\LaravelNagiosNrdp\NagiosNrdp;

class AnalyticsServiceProviderTest extends TestCase
{
    /**
     * @test
     */
    public function object_should_resolve_from_container()
    {
        $nagiosNrdp = $this->app['laravel-nagios-nrdp'];

        $this->assertInstanceOf(NagiosNrdp::class, $nagiosNrdp);
    }

    /**
     * @test
     */
    public function sending_a_message()
    {
        $this->app['config']->set('laravel-nagios-nrdp.url', 'https://laravel-nagios-nrdp.test');
        $this->app['config']->set('laravel-nagios-nrdp.host', 'testhost');
        $this->app['config']->set('laravel-nagios-nrdp.token', 'testtoken');

        /** @var NagiosNrdp $nagiosNrdp */
        $nagiosNrdp = $this->app['laravel-nagios-nrdp'];

        $result = $nagiosNrdp
            ->state(NagiosNrdp::HOST_DOWN)
            ->send('test');
    }
}