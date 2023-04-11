<?php

namespace Rguj\Laracore\Trait\Eloquent;

trait PreventsCreating
{
	public static function bootPreventsCreating()
    {
        static::creating(function($model) {
            return false;
        });
    }
}