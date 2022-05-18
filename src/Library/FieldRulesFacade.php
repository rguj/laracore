<?php

namespace Rguj\Laracore\Library;

use Illuminate\Support\Facades\Facade;

class FieldRulesFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'FieldRules';
    }
}