<?php

namespace KonnectIT\LaravelNagiosNrdp;

use Illuminate\Support\Facades\Facade;

/**
 * @see \KonnectIT\LaravelNagiosNrdp\LaravelNagiosNrdp
 */
class LaravelNagiosNrdpFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-nagios-nrdp';
    }
}
