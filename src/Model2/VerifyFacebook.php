<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Rguj\Laracore\Model\BMVerifyFacebook;

class VerifyFacebook extends BMVerifyFacebook
{
    protected $connection = 'mysql';
	
}