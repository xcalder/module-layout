<?php

namespace ModuleLayout\Facades;

use Illuminate\Support\Facades\Facade;
use ModuleLayout\Factory\Factory;

/**
 * @see \Laravel\Socialite\SocialiteManager
 */
class ModuleLayout extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }
}
