<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Rguj\Laracore\Model\BMVerifyGoogle;

class VerifyGoogle extends BMVerifyGoogle
{
    protected $connection = 'mysql';
	
}