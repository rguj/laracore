<?php

namespace Rguj\Laracore\Trait\Eloquent;

trait PreventsDeleting
{
	public static function bootPreventsDeleting()
    {
        static::deleting(function($model) {
            return false;
        });
    }
}