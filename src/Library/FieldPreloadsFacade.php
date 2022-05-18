<?php

namespace Rguj\Laracore\Library;

use Illuminate\Support\Facades\Facade;

class FieldPreloadsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'FieldPreloads';
    }
}