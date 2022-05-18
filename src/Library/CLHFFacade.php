<?php

namespace Rguj\Laracore\Library;

use Illuminate\Support\Facades\Facade;

class CLHFFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'CLHF';
    }
}