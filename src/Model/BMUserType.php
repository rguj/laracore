<?php
namespace Rguj\Laracore\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Rguj\Laracore\Model\BaseModel;

class BMUserType extends BaseModel
{
    use HasFactory;

    //protected $connection = '';
    //protected $table = 'user_type';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    public $timestamps = true;
    protected $dateFormat  = 'Y-m-d H:i:s.u';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    protected $fillable = [
        'user_id',
        'role_id',
        'is_valid',
    ];

    protected $hidden = [
        'user_id',
    ];

    protected $attributes = [
        
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];


    public function user() {  // ok
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    public function role() {
        return $this->belongsTo(\App\Models\Role::class, 'id', 'user_role_id');
    }


}
