<?php

namespace Rguj\Laracore\Trait\Eloquent;

trait PreventsUpdating
{
	public static function bootPreventsUpdating()
    {
        static::updating(function($model) {
            return false;
        });
    }
}