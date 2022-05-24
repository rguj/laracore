<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Rguj\Laracore\Model\BMPermission;

class Permission extends BMPermission
{
    protected $connection = 'mysql';
	protected $table = 'unv_permission';
	
}