<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Rguj\Laracore\Model\BMAPI;

class API extends BMAPI
{
    protected $connection = 'mysql';
	protected $table = 'unv_api';
	
}
