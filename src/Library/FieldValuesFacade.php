<?php

namespace Rguj\Laracore\Library;

use Illuminate\Support\Facades\Facade;

class FieldValuesFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'FieldValues';
    }
}