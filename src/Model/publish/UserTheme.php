<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Rguj\Laracore\Model\BMUserTheme;

class UserTheme extends BMUserTheme
{
    protected $connection = 'mysql';	
	protected $table = 'user_theme';
	
}