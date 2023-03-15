<?php

namespace Rguj\Laracore\Traits\Eloquent;

trait PreventsDeleting
{
	public static function bootPreventsDeleting()
    {
        static::deleting(function($model) {
            return false;
        });
    }
}