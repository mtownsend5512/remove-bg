<?php

namespace Mtownsend\RemoveBg\Facades;

use Illuminate\Support\Facades\Facade;

class RemoveBg extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'removebg';
    }
}
