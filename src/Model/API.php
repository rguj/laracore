<?php

namespace Rguj\Laracore\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Rguj\Laracore\Model\BaseModel;

class API extends BaseModel
{
    // use HasFactory;

    protected $connection = 'hris';
    protected $table = 'ac_api';
    protected $primaryKey = 'id';
    protected $keyType = 'integer';
    public $incrementing = true;

    public $timestamps = true;
    protected $dateFormat  = 'Y-m-d H:i:s.u';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    protected $fillable = [
        
    ];

    protected $hidden = [
        
    ];

    protected $attributes = [
        
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];



    // public function role() {
    //     return $this->hasOne(\App\Models\Role::class, 'id', 'role_id');
    // }


}
