<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Rguj\Laracore\Model\BMProgram;

class Program extends BMProgram
{
    protected $connection = 'mysql';
	protected $table = 'pl_programs';
	
}
