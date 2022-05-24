<?php
namespace Rguj\Laracore\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Rguj\Laracore\Model\BaseModel;

use Spatie\Permission\Models\Role as BaseRole;

class BMRole extends BaseRole
{
    use HasFactory;

    //protected $connection = '';
    protected $table = 'unv_role';
    protected $primaryKey = 'id';
    protected $keyType = 'integer';
    public $incrementing = true;

    public $timestamps = true;
    protected $dateFormat  = 'Y-m-d H:i:s.u';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    protected $fillable = [
        'name',
        'short',
        'guard_name',
        'is_valid',

    ];

    protected $hidden = [
        
    ];

    protected $attributes = [
        
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];



    public function menu() {
        return $this->hasOne(\App\Models\Menu::class, 'role_id', 'id');
    }

    // public function types() {
    //     return $this->hasMany(\App\Models\UserType::class, 'user_role_id', 'id');
    // }


}
