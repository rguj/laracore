<?php

namespace Rguj\Laracore\Library\Facade;

use Illuminate\Support\Facades\Facade;

class WebClientFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'WebClient';
    }
}