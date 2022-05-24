<?php

namespace Rguj\Laracore\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Rguj\Laracore\Model\BaseModel;

class VerifyGoogle extends BaseModel
{
    use HasFactory;

    protected $connection = 'hris';
    protected $table = 'ac_verify_google';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    public $timestamps = true;
    protected $dateFormat  = 'Y-m-d H:i:s.u';
    // const CREATED_AT = 'created_at';
    // const UPDATED_AT = 'updated_at';
    
    protected $fillable = [
        'uuid',
        'calls',
        'lastcall_at',
        'created_at',
    ];

    protected $hidden = [
        
    ];

    protected $attributes = [
        
    ];

    protected $casts = [
        'lastcall_at' => 'datetime:Y-m-d H:i:s.u',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
    ];






    public function user() {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }


}
