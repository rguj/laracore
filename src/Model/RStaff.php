<?php

namespace Rguj\Laracore\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Rguj\Laracore\Model\BaseModel;

class RStaff extends BaseModel
{
    use HasFactory;

    protected $connection = 'hris';
    protected $table = 'cd_rstaff';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    public $timestamps = true;
    protected $dateFormat  = 'Y-m-d H:i:s.u';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    protected $fillable = [
        'program_id',
        'user_id',
        'is_active',
    ];

    protected $hidden = [
        
    ];

    protected $attributes = [
        
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];






    public function user() {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    public function programs() {
        return $this->hasMany(\App\Models\Program::class, 'id', 'program_id');
    }


}
