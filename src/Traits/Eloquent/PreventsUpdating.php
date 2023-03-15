<?php

namespace Rguj\Laracore\Traits\Eloquent;

trait PreventsUpdating
{
	public static function bootPreventsUpdating()
    {
        static::updating(function($model) {
            return false;
        });
    }
}