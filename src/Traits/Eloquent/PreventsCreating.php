<?php

namespace Rguj\Laracore\Traits\Eloquent;

trait PreventsCreating
{
	public static function bootPreventsCreating()
    {
        static::creating(function($model) {
            return false;
        });
    }
}