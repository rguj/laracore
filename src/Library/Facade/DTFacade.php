<?php

namespace Rguj\Laracore\Library\Facade;

use Illuminate\Support\Facades\Facade;

class DTFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'DT';
    }
}