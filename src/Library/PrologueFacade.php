<?php

namespace Rguj\Laracore\Library;

use Illuminate\Support\Facades\Facade;

class PrologueFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Prologue';
    }
}