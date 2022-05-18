<?php

namespace Rguj\Laracore\Library;

use Illuminate\Support\Facades\Facade;

class AppFnFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'AppFn';
    }
}