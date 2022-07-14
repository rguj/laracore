<?php
namespace Rguj\Laracore\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Rguj\Laracore\Model\BaseModel;

class BMModelRole extends BaseModel
{
    use HasFactory;

    //protected $connection = '';
    //protected $table = 'unv_user_role';
    // protected $primaryKey = 'id';
    // protected $keyType = 'integer';
    // public $incrementing = false;

    public $timestamps = false;
    // protected $dateFormat  = 'Y-m-d H:i:s.u';
    // const CREATED_AT = 'created_at';
    // const UPDATED_AT = 'updated_at';
    
    protected $fillable = [
        'model_id',
        'role_id',
        'model_type',
    ];

    protected $hidden = [
        
    ];

    protected $attributes = [
        
    ];

    protected $casts = [
        // 'created_at' => 'datetime:Y-m-d H:i:s.u',
        // 'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];



    // public function users() {
    //     return $this->hasMany(\App\Models\User::class, 'user_id', 'id');
    // }

    // public function roles() {
    //     return $this->hasMany(\App\Models\Role::class, 'role_id', 'id');
    // }

}
