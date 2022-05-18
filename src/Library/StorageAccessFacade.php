<?php

namespace Rguj\Laracore\Library;

use Illuminate\Support\Facades\Facade;

class StorageAccessFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'StorageAccess';
    }
}