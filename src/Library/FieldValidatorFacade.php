<?php

namespace Rguj\Laracore\Library;

use Illuminate\Support\Facades\Facade;

class FieldValidatorFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'FieldValidator';
    }
}