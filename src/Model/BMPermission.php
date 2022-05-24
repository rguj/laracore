<?php
namespace Rguj\Laracore\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Rguj\Laracore\Model\BaseModel;

use Spatie\Permission\Models\Permission as BasePermission;

class BMPermission extends BasePermission
{
    use HasFactory;

    //protected $connection = '';
    //protected $table = 'unv_permission';
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






}
